<?php

namespace App\Utils\AdVendors;

use App\Endpoints\TaboolaAPI;
use App\Jobs\PullCampaign;
use App\Models\Ad;
use App\Models\Campaign;
use App\Models\Provider;
use App\Models\RedtrackContentStat;
use App\Models\RedtrackDomainStat;
use App\Models\RedtrackReport;
use App\Models\TaboolaReport;
use App\Models\UserProvider;
use App\Models\UserTracker;
use App\Models\CreativeSet;
use App\Vngodev\AdVendorInterface;
use App\Vngodev\Helper;
use App\Vngodev\ResourceImporter;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;

class Taboola extends Root implements AdVendorInterface
{
    use \App\Utils\AdVendors\Attributes\Taboola;

    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->orWhere('id', request('provider'))->first();

        return new TaboolaAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first());
    }

    public function advertisers()
    {
        return $this->api()->getAdvertisers()['results'];
    }

    public function countries()
    {
        return $this->api()->getCountries()['results'];
    }

    public function store()
    {
        $api = $this->api();

        try {
            $data = [
                'name' => request('campaignName'),
                'branding_text' => request('campaignBrandText'),
                'cpc' => request('campaignCPC'),
                'spending_limit' => request('campaignSpendingLimit'),
                'spending_limit_model' => request('campaignSpendingLimitModel'),
                'marketing_objective' => request('campaignMarketingObjective'),
                'is_active' => request('campaignIsActive'),
                'start_date' => request('campaignStartDate'),
                'end_date' => request('campaignEndDate')
            ];

            $country_targeting = request('campaignCountryTargeting');
            $platform_targeting = request('campaignPlatformTargeting');

            if (count($country_targeting) > 0) {
                $data['country_targeting'] = [
                    'type' => 'INCLUDE',
                    'value' => $country_targeting
                ];
            }

            if (count($platform_targeting) > 0) {
                $data['platform_targeting'] = [
                    'type' => 'INCLUDE',
                    'value' => $platform_targeting
                ];
            }

            $campaign_data = $api->createCampaign(request('advertiser'), $data);

            foreach (request('campaignItems') as $campaign_item) {
                $titles = [];

                $title_creative_set = null;
                $description_creative_set = null;
                $image_creative_set = null;
                $video_creative_set = null;

                if (isset($campaign_item['titleSet']['id'])) {
                    $title_creative_set = CreativeSet::find($campaign_item['titleSet']['id']);

                    if ($title_creative_set) {
                        $titles = $title_creative_set->titleSets;
                    } else {
                        throw new Exception('No creative set found.');
                    }
                } else {
                    $titles = $campaign_item['titles'];
                }

                $description = '';

                if (isset($campaign_item['descriptionSet']['id'])) {
                    $description_creative_set = CreativeSet::find($campaign_item['descriptionSet']['id']);

                    if ($description_creative_set) {
                        $description = $description_creative_set->descriptionSets[0]['description'];
                    } else {
                        throw new Exception('No creative set found.');
                    }
                } else {
                    $description = $campaign_item['description'];
                }

                foreach ($titles as $title) {
                    if ($campaign_item['adType'] == 'IMAGE') {
                        $imges = [];

                        if (isset($campaign_item['imageSet']['id'])) {
                            $image_creative_set = CreativeSet::find($campaign_item['imageSet']['id']);

                            if ($image_creative_set) {
                                $images = $image_creative_set->imageSets;
                            } else {
                                throw new Exception('No creative set found.');
                            }
                        } else {
                            $images = $campaign_item['images'];
                        }

                        foreach ($images as $image) {
                            $campaign_item_data = $api->createCampaignItem(request('advertiser'), $campaign_data['id'], $campaign_item['url']);

                            $db_ad = Ad::firstOrNew([
                                'ad_id' => $campaign_item_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 4,
                                'campaign_id' => $campaign_data['id'],
                                'advertiser_id' => request('advertiser'),
                                'ad_group_id' => 'taboola',
                                'open_id' => request('account')
                            ]);

                            $db_ad->name = $title['title'];
                            $db_ad->type = 1;
                            $db_ad->image = Helper::encodeUrl($image_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $image['image']) : $image['image']);
                            $db_ad->status = $campaign_item_data['status'];
                            $db_ad->description = $description;
                            $db_ad->synced = 0;

                            $db_ad->save();

                            $db_ad->creativeSets()->detach();

                            if ($title_creative_set) {
                                $db_ad->creativeSets()->save($title_creative_set);
                            }

                            if ($description_creative_set) {
                                $db_ad->creativeSets()->save($description_creative_set);
                            }

                            if ($video_creative_set) {
                                $db_ad->creativeSets()->save($video_creative_set);
                            }

                            if ($image_creative_set) {
                                $db_ad->creativeSets()->save($image_creative_set);
                            }
                        }
                    } else {
                        $videos = [];

                        if (isset($campaign_item['videoSet']['id'])) {
                            $video_creative_set = CreativeSet::find($campaign_item['videoSet']['id']);

                            if ($video_creative_set) {
                                $videos = $video_creative_set->videoSets;
                            } else {
                                throw new Exception('No creative set found.');
                            }
                        } else {
                            $videos = $campaign_item['videos'];
                        }

                        foreach ($videos as $video) {
                            $campaign_item_data = $api->createCampaignVideoItem(request('advertiser'), $campaign_data['id'], [
                                'url' => $campaign_item['url'],
                                'title' => $title['title'],
                                'description' => $description,
                                'video_url' => Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['video']) : $video['videoUrl']),
                                'fallback_url' => $video['imageUrl']
                            ]);

                            $db_ad = Ad::firstOrNew([
                                'ad_id' => $campaign_item_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 4,
                                'campaign_id' => $campaign_data['id'],
                                'ad_group_id' => 'taboola',
                                'advertiser_id' => request('advertiser'),
                                'open_id' => request('account')
                            ]);

                            $db_ad->name = $title['title'];
                            $db_ad->type = 2;
                            $db_ad->video = Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['video']) : $video['videoUrl']);
                            $db_ad->image = Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['landscape_image']) : $video['imageUrl']);
                            $db_ad->status = $campaign_item_data['status'];
                            $db_ad->description = $description;
                            $db_ad->synced = 1;

                            $db_ad->save();

                            $db_ad->creativeSets()->detach();

                            if ($title_creative_set) {
                                $db_ad->creativeSets()->save($title_creative_set);
                            }

                            if ($description_creative_set) {
                                $db_ad->creativeSets()->save($description_creative_set);
                            }

                            if ($video_creative_set) {
                                $db_ad->creativeSets()->save($video_creative_set);
                            }

                            if ($image_creative_set) {
                                $db_ad->creativeSets()->save($image_creative_set);
                            }
                        }
                    }
                }
            }

            $resource_importer = new ResourceImporter();

            $resource_importer->insertOrUpdate('campaigns', [[
                'campaign_id' => $campaign_data['id'],
                'provider_id' => 4,
                'user_id' => auth()->id(),
                'open_id' => request('account'),
                'advertiser_id' => request('advertiser'),
                'name' => $campaign_data['name'],
                'status' => $campaign_data['status']
            ]], ['campaign_id', 'provider_id', 'user_id', 'open_id', 'advertiser_id']);

            Helper::pullCampaign();

            return $campaign_data;
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function update(Campaign $campaign)
    {
        $api = new TaboolaAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

        try {
            $data = [
                'name' => request('campaignName'),
                'branding_text' => request('campaignBrandText'),
                'cpc' => request('campaignCPC'),
                'spending_limit' => request('campaignSpendingLimit'),
                'spending_limit_model' => request('campaignSpendingLimitModel'),
                'marketing_objective' => request('campaignMarketingObjective'),
                'is_active' => request('campaignIsActive'),
                'start_date' => request('campaignStartDate'),
                'end_date' => request('campaignEndDate')
            ];

            $country_targeting = request('campaignCountryTargeting');
            $platform_targeting = request('campaignPlatformTargeting');

            if (count($country_targeting) > 0) {
                $data['country_targeting'] = [
                    'type' => 'INCLUDE',
                    'value' => $country_targeting
                ];
            }

            if (count($platform_targeting) > 0) {
                $data['platform_targeting'] = [
                    'type' => 'INCLUDE',
                    'value' => $platform_targeting
                ];
            }

            $campaign_data = $api->updateCampaign($campaign->advertiser_id, $campaign->campaign_id, $data);

            foreach (request('campaignItems') as $campaign_item) {
                $titles = [];

                $title_creative_set = null;
                $description_creative_set = null;
                $image_creative_set = null;
                $video_creative_set = null;

                if (isset($campaign_item['titleSet']['id'])) {
                    $title_creative_set = CreativeSet::find($campaign_item['titleSet']['id']);

                    if ($title_creative_set) {
                        $titles = $title_creative_set->titleSets;
                    } else {
                        throw new Exception('No creative set found.');
                    }
                } else {
                    $titles = $campaign_item['titles'];
                }

                $description = '';

                if (isset($campaign_item['descriptionSet']['id'])) {
                    $description_creative_set = CreativeSet::find($campaign_item['descriptionSet']['id']);

                    if ($description_creative_set) {
                        $description = $description_creative_set->descriptionSets[0]['description'];
                    } else {
                        throw new Exception('No creative set found.');
                    }
                } else {
                    $description = $campaign_item['description'];
                }

                foreach ($titles as $title) {
                    if ($campaign_item['adType'] == 'IMAGE') {
                        $imges = [];

                        if (isset($campaign_item['imageSet']['id'])) {
                            $image_creative_set = CreativeSet::find($campaign_item['imageSet']['id']);

                            if ($image_creative_set) {
                                $images = $image_creative_set->imageSets;
                            } else {
                                throw new Exception('No creative set found.');
                            }
                        } else {
                            $images = $campaign_item['images'];
                        }

                        foreach ($images as $image) {
                            $synced = 0;
                            if (isset($campaign_item['id'])) {
                                $campaign_item_data = $api->updateCampaignItem($campaign->advertiser_id, $campaign->campaign_id, $campaign_item['id'], [
                                    'url' => $campaign_item['url'],
                                    'title' => $title['title'],
                                    'description' => $campaign_item['description'],
                                    'thumbnail_url' => Helper::encodeUrl($image_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $image['image']) : $image['image'])
                                ]);

                                $synced = 1;
                            } else {
                                $campaign_item_data = $api->createCampaignItem(request('advertiser'), $campaign_data['id'], $campaign_item['url']);
                            }

                            $db_ad = Ad::firstOrNew([
                                'ad_id' => $campaign_item_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 4,
                                'campaign_id' => $campaign_data['id'],
                                'advertiser_id' => request('advertiser'),
                                'ad_group_id' => 'taboola',
                                'open_id' => request('account')
                            ]);

                            $db_ad->synced = $synced;
                            $db_ad->type = 1;
                            $db_ad->name = $title['title'];
                            $db_ad->image = Helper::encodeUrl($image_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $image['image']) : $image['image']);
                            $db_ad->status = $campaign_item_data['status'];
                            $db_ad->description = $campaign_item['description'];

                            $db_ad->save();

                            $db_ad->creativeSets()->detach();

                            if ($title_creative_set) {
                                $db_ad->creativeSets()->save($title_creative_set);
                            }

                            if ($description_creative_set) {
                                $db_ad->creativeSets()->save($description_creative_set);
                            }

                            if ($video_creative_set) {
                                $db_ad->creativeSets()->save($video_creative_set);
                            }

                            if ($image_creative_set) {
                                $db_ad->creativeSets()->save($image_creative_set);
                            }
                        }
                    } else {
                        $videos = [];

                        if (isset($content['videoSet']['id'])) {
                            $video_creative_set = CreativeSet::find($content['videoSet']['id']);

                            if ($video_creative_set) {
                                $videos = $video_creative_set->videoSets;
                            } else {
                                throw new Exception('No creative set found.');
                            }
                        } else {
                            $videos = $content['videos'];
                        }

                        foreach ($videos as $video) {
                            if (isset($campaign_item['id'])) {
                                $campaign_item_data = $api->updateCampaignVideoItem($campaign->advertiser_id, $campaign->campaign_id, $campaign_item['id'], [
                                    'url' => $campaign_item['url'],
                                    'title' => $title['title'],
                                    'description' => $description,
                                    'video_url' => Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['video']) : $video['videoUrl']),
                                    'fallback_url' => Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['landscape_image']) : $video['imageUrl'])
                                ]);
                            } else {
                                $campaign_item_data = $api->createCampaignVideoItem(request('advertiser'), $campaign_data['id'], [
                                    'url' => $campaign_item['url'],
                                    'title' => $title['title'],
                                    'description' => $description,
                                    'video_url' => Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['video']) : $video['videoUrl']),
                                    'fallback_url' => Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['landscape_image']) : $video['imageUrl'])
                                ]);
                            }

                            $db_ad = Ad::firstOrNew([
                                'ad_id' => $campaign_item_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 4,
                                'campaign_id' => $campaign_data['id'],
                                'ad_group_id' => 'taboola',
                                'advertiser_id' => request('advertiser'),
                                'open_id' => request('account')
                            ]);

                            $db_ad->type = 2;
                            $db_ad->name = $title['title'];
                            $db_ad->video = Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['video']) : $video['videoUrl']);
                            $db_ad->image = Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['landscape_image']) : $video['imageUrl']);
                            $db_ad->status = $campaign_item_data['status'];
                            $db_ad->description = $description;
                            $db_ad->synced = 1;

                            $db_ad->save();

                            $db_ad->creativeSets()->detach();

                            if ($title_creative_set) {
                                $db_ad->creativeSets()->save($title_creative_set);
                            }

                            if ($description_creative_set) {
                                $db_ad->creativeSets()->save($description_creative_set);
                            }

                            if ($video_creative_set) {
                                $db_ad->creativeSets()->save($video_creative_set);
                            }

                            if ($image_creative_set) {
                                $db_ad->creativeSets()->save($image_creative_set);
                            }
                        }
                    }
                }
            }

            return $campaign_data;
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function itemStatus()
    {
        try {
            $campaign_items = $this->api()->getCampaignItems(request('advertiser'), request('campaignId'));

            foreach ($campaign_items['results'] as $campaign_item) {
                if ($campaign_item['status'] == 'CRAWLING') {
                    return [
                        'status' => false
                    ];
                }
            }

            $campaign = Campaign::where('campaign_id', request('campaignId'))->first();

            return [
                'status' => true,
                'campaign_id' => $campaign->id
            ];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function storeAd(Campaign $campaign, $ad_group_id)
    {
        $api = $this->api();

        $ads = [];

        try {
            foreach (request('campaignItems') as $campaign_item) {
                foreach ($campaign_item['titles'] as $title) {
                    if ($campaign_item['adType'] == 'IMAGE') {
                        foreach ($campaign_item['images'] as $image) {
                            $campaign_item_data = $api->createCampaignItem($campaign->advertiser_id, $campaign->campaign_id, $campaign_item['url']);
                            $ads[] = [
                                'ad_id' => $campaign_item_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 4,
                                'campaign_id' => $campaign->campaign_id,
                                'ad_group_id' => 'taboola',
                                'advertiser_id' => $campaign->advertiser_id,
                                'open_id' => request('account'),
                                'type' => 1,
                                'name' => $title['title'],
                                'image' => $image['image'],
                                'status' => $campaign_item_data['status'],
                                'description' => $campaign_item['description'],
                                'synced' => 0,
                                'url' => $campaign_item['url']
                            ];
                        }
                    } else {
                        foreach ($campaign_item['videos'] as $video) {
                            $campaign_item_data = $api->createCampaignVideoItem($campaign->advertiser_id, $campaign->campaign_id, [
                                'url' => $campaign_item['url'],
                                'title' => $title['title'],
                                'description' => $campaign_item['description'],
                                'video_url' => $video['videoUrl'],
                                'fallback_url' => $video['imageUrl']
                            ]);

                            $ads[] = [
                                'ad_id' => $campaign_item_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 4,
                                'campaign_id' => $campaign->campaign_id,
                                'ad_group_id' => 'taboola',
                                'advertiser_id' => $campaign->advertiser_id,
                                'open_id' => request('account'),
                                'type' => 2,
                                'name' => $title['title'],
                                'video' => $video['videoUrl'],
                                'image' => $video['imageUrl'],
                                'status' => $campaign_item_data['status'],
                                'description' => $campaign_item['description'],
                                'synced' => 1,
                                'url' => $campaign_item['url']
                            ];
                        }
                    }
                }
            }

            $resource_importer = new ResourceImporter();
            $resource_importer->insertOrUpdate('ads', $ads, ['ad_id', 'user_id', 'provider_id', 'campaign_id', 'advertiser_id', 'ad_group_id', 'open_id']);

            return $ads;
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function updateAd(Campaign $campaign, $ad_group_id)
    {
        $api = $this->api();

        $ads = [];

        try {
            foreach (request('campaignItems') as $campaign_item) {
                foreach ($campaign_item['titles'] as $title) {
                    if ($campaign_item['adType'] == 'IMAGE') {
                        foreach ($campaign_item['images'] as $image) {
                            $synced = 1;
                            if ($title['existing'] && $image['existing']) {
                                $campaign_item_data = $api->updateCampaignItem($campaign->advertiser_id, $campaign->campaign_id, $campaign_item['id'], [
                                    'url' => $campaign_item['url'],
                                    'title' => $title['title'],
                                    'description' => $campaign_item['description'],
                                    'thumbnail_url' => $image['image']
                                ]);
                            } else {
                                $campaign_item_data = $api->createCampaignItem($campaign->advertiser_id, $campaign->campaign_id, $campaign_item['url']);

                                $synced = 0;
                            }

                            $ads[] = [
                                'ad_id' => $campaign_item_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 4,
                                'campaign_id' => $campaign->campaign_id,
                                'ad_group_id' => 'taboola',
                                'advertiser_id' => $campaign->advertiser_id,
                                'open_id' => request('account'),
                                'name' => $title['title'],
                                'image' => $image['image'],
                                'status' => $campaign_item_data['status'],
                                'description' => $campaign_item['description'],
                                'synced' => $synced,
                                'url' => $campaign_item['url']
                            ];
                        }
                    } else {
                        foreach ($campaign_item['videos'] as $video) {
                            if ($title['existing'] && $video['existing']) {
                                $campaign_item_data = $api->updateCampaignVideoItem($campaign->advertiser_id, $campaign->campaign_id, $campaign_item['id'], [
                                    'url' => $campaign_item['url'],
                                    'title' => $title['title'],
                                    'description' => $campaign_item['description'],
                                    'video_url' => $video['videoUrl'],
                                    'fallback_url' => $video['imageUrl']
                                ]);
                            } else {
                                $campaign_item_data = $api->createCampaignVideoItem($campaign->advertiser_id, $campaign->campaign_id, [
                                    'url' => $campaign_item['url'],
                                    'title' => $title['title'],
                                    'description' => $campaign_item['description'],
                                    'video_url' => $video['videoUrl'],
                                    'fallback_url' => $video['imageUrl']
                                ]);
                            }

                            $ads[] = [
                                'ad_id' => $campaign_item_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 4,
                                'campaign_id' => $campaign->campaign_id,
                                'ad_group_id' => 'taboola',
                                'advertiser_id' => $campaign->advertiser_id,
                                'open_id' => request('account'),
                                'type' => 2,
                                'name' => $title['title'],
                                'video' => $video['videoUrl'],
                                'image' => $video['imageUrl'],
                                'status' => $campaign_item_data['status'],
                                'description' => $campaign_item['description'],
                                'synced' => 1,
                                'url' => $campaign_item['url']
                            ];
                        }
                    }
                }
            }

            $resource_importer = new ResourceImporter();
            $resource_importer->insertOrUpdate('ads', $ads, ['ad_id', 'user_id', 'provider_id', 'campaign_id', 'advertiser_id', 'ad_group_id', 'open_id']);

            return $ads;
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncAd(Ad $ad)
    {
        $api = new TaboolaAPI(UserProvider::where('provider_id', $ad->provider_id)->where('open_id', $ad->open_id)->first());
        $campaign_item_data = $api->updateCampaignItem($ad->advertiser_id, $ad->campaign_id, $ad->ad_id, [
            'title' => $ad['name'],
            'description' => $ad['description'],
            'thumbnail_url' => $ad['image']
        ]);

        $ad->synced = 1;
        $ad->save();
    }

    public function pullCampaign($user_provider)
    {
        $api = new TaboolaAPI($user_provider);
        $db_campaigns = [];

        $resource_importer = new ResourceImporter();

        $updated_at = Carbon::now();

        foreach ($user_provider->advertisers as $advertiser) {
            $campaigns = $api->getCampaigns($advertiser)['results'];

            foreach ($campaigns as $campaign) {
                $db_campaigns[] = [
                    'campaign_id' => $campaign['id'],
                    'provider_id' => $user_provider->provider_id,
                    'user_id' => $user_provider->user_id,
                    'open_id' => $user_provider->open_id,
                    'advertiser_id' => $advertiser,
                    'name' => $campaign['name'],
                    'status' => $campaign['status'] === 'RUNNING' ? 'ACTIVE' : $campaign['status'],
                    'budget' => $campaign['spending_limit'],
                    'updated_at' => $updated_at
                ];
            }
        }

        if (count($db_campaigns)) {
            $resource_importer->insertOrUpdate('campaigns', $db_campaigns, ['campaign_id', 'provider_id', 'user_id', 'open_id', 'advertiser_id']);

            Campaign::where([
                'user_id' => $user_provider->user_id,
                'provider_id' => $user_provider->provider_id,
                'open_id' => $user_provider->open_id
            ])->where('updated_at', '<>', $updated_at)->delete();
        }
    }

    public function pullAdGroup($user_provider)
    {
        //
    }

    public function pullAd($user_provider)
    {
        $api = new TaboolaAPI($user_provider);
        $db_ads = [];

        $resource_importer = new ResourceImporter();

        $updated_at = Carbon::now();

        Campaign::where('user_id', $user_provider->user_id)->where('provider_id', $user_provider->provider_id)->chunk(10, function ($campaigns) use ($resource_importer, $api, $user_provider, &$db_ads, $updated_at) {
            foreach ($campaigns as $key => $campaign) {
                $campaign_items = $api->getCampaignItems($campaign->advertiser_id, $campaign->campaign_id)['results'];

                if ($campaign_items && count($campaign_items)) {
                    foreach ($campaign_items as $campaign_item) {
                        $db_ads[] = [
                            'ad_id' => $campaign_item['id'],
                            'user_id' => $user_provider->user_id,
                            'provider_id' => $user_provider->provider_id,
                            'campaign_id' => $campaign->campaign_id,
                            'ad_group_id' => 'taboola',
                            'advertiser_id' => $campaign->advertiser_id,
                            'open_id' => $user_provider->open_id,
                            'name' => $campaign_item['title'] ?? 'NA',
                            'status' => $campaign_item['status'] === 'RUNNING' ? 'ACTIVE' : $campaign_item['status'],
                            'image' => $campaign_item['thumbnail_url'] ?? $campaign_item['fallback_url'] ?? null,
                            'updated_at' => $updated_at
                        ];
                    }
                }
            }
        });

        if (count($db_ads)) {
            $resource_importer->insertOrUpdate('ads', $db_ads, ['ad_id', 'user_id', 'provider_id', 'campaign_id', 'advertiser_id', 'ad_group_id', 'open_id']);
            Ad::where([
                'user_id' => $user_provider->user_id,
                'provider_id' => $user_provider->provider_id,
                'open_id' => $user_provider->open_id
            ])->where('updated_at', '<>', $updated_at)->delete();
        }
    }

    public function delete(Campaign $campaign)
    {
        try {
            $api = new TaboolaAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());
            $api->deleteCampaign($campaign->advertiser_id, $campaign->campaign_id);
            $campaign->delete();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function getCampaignInstance(Campaign $campaign)
    {
        try {
            $api = new TaboolaAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

            $instance = $api->getCampaign($campaign->advertiser_id, $campaign->campaign_id);

            $instance['provider'] = $campaign->provider->slug;
            $instance['provider_id'] = $campaign['provider_id'];
            $instance['open_id'] = $campaign['open_id'];
            $instance['instance_id'] = $campaign['id'];
            $campaign_items = $api->getCampaignItems($campaign->advertiser_id, $campaign->campaign_id)['results'];

            $instance['items'] = [];

            foreach ($campaign_items as $item) {
                $ad = Ad::where(['ad_id' => $item['id'], 'type' => 1])->first();

                if ($ad) {
                    $item['type'] = 1;

                    if (empty($item['title'])) {
                        $item['title'] = $ad['title'];
                    }
                    if (empty($item['description'])) {
                        $item['description'] = $ad['description'];
                    }
                    if (empty($item['thumbnail_url'])) {
                        $item['thumbnail_url'] = $ad['image'];
                    }

                    $image_set = $ad->creativeSets()->where('type', 1)->first();

                    if ($image_set) {
                        $item['imageSet'] = $image_set;
                        $item['imageSet']['sets'] = $image_set->imageSets;
                    }

                    $video_set = $ad->creativeSets()->where('type', 2)->first();
                    if ($video_set) {
                        $item['videoSet'] = $video_set;
                        $item['videoSet']['sets'] = $video_set->videoSets;
                    }

                    $title_set = $ad->creativeSets()->where('type', 3)->first();
                    if ($title_set) {
                        $item['titleSet'] = $title_set;
                        $item['titleSet']['sets'] = $title_set->titleSets;
                    }

                    $description_set = $ad->creativeSets()->where('type', 4)->first();
                    if ($description_set) {
                        $item['descriptionSet'] = $description_set;
                        $item['descriptionSet']['sets'] = $description_set->descriptionSets;
                    }

                    $instance['items'][] = $item;
                }
            }

            $campaign_items = $api->getCampaignVideoItems($campaign->advertiser_id, $campaign->campaign_id)['results'];

            foreach ($campaign_items as $item) {
                $ad = Ad::where([
                    'ad_id' => $item['id'],
                    'type' => 2
                ])->first();

                if ($ad) {
                    $item['type'] = 2;

                    if (empty($item['title'])) {
                        $item['title'] = $ad['title'];
                    }
                    if (empty($item['description'])) {
                        $item['description'] = $ad['description'];
                    }

                    if (empty($item['title'])) {
                        $item['title'] = $ad['title'];
                    }
                    if (empty($item['description'])) {
                        $item['description'] = $ad['description'];
                    }
                    if (empty($item['thumbnail_url'])) {
                        $item['thumbnail_url'] = $ad['image'];
                    }

                    $image_set = $ad->creativeSets()->where('type', 1)->first();

                    if ($image_set) {
                        $item['imageSet'] = $image_set;
                        $item['imageSet']['sets'] = $image_set->imageSets;
                    }

                    $video_set = $ad->creativeSets()->where('type', 2)->first();
                    if ($video_set) {
                        $item['videoSet'] = $video_set;
                        $item['videoSet']['sets'] = $video_set->videoSets;
                    }

                    $title_set = $ad->creativeSets()->where('type', 3)->first();
                    if ($title_set) {
                        $item['titleSet'] = $title_set;
                        $item['titleSet']['sets'] = $title_set->titleSets;
                    }

                    $description_set = $ad->creativeSets()->where('type', 4)->first();
                    if ($description_set) {
                        $item['descriptionSet'] = $description_set;
                        $item['descriptionSet']['sets'] = $description_set->descriptionSets;
                    }

                    $instance['items'][] = $item;
                }
            }

            return $instance;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAdInstance(Campaign $campaign, $ad_group_id, $ad_id)
    {
        try {
            $api = new TaboolaAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

            $ad = $api->getCampaignItem($campaign->advertiser_id, $campaign->campaign_id, $ad_id);
            $ad['open_id'] = $campaign['open_id'];

            return $ad;
        } catch (Exception $e) {
            return [];
        }
    }

    public function cloneCampaignName(&$instance)
    {
        //
    }

    public function cloneAdName(&$instance)
    {
        if ($instance['title']) {
            $instance['title'] = $instance['title'] . ' - Copy';
        }
    }

    public function status(Campaign $campaign)
    {
        //
    }

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id, $status = null)
    {
        //
    }

    public function pullRedTrack($user_provider, $target_date = null)
    {
        $tracker = UserTracker::where('provider_id', $user_provider->provider_id)->where('provider_open_id', $user_provider->open_id)->first();

        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            if ($target_date) {
                $date = $target_date;
            }
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub6,hour_of_day&sub9=Taboola&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);
            if (count($data)) {
                foreach ($data as $key => $value) {
                    $campaigns = Campaign::where('campaign_id', $value['sub6'])->get();
                    foreach ($campaigns as $index => $campaign) {
                        $value['date'] = $date;
                        $value['user_id'] = $campaign->user_id;
                        $value['campaign_id'] = $campaign->id;
                        $value['provider_id'] = $campaign->provider_id;
                        $value['open_id'] = $campaign->open_id;
                        $value['advertiser_id'] = $campaign->advertiser_id;
                        $redtrack_report = RedtrackReport::firstOrNew([
                            'date' => $date,
                            'sub6' => $campaign->campaign_id,
                            'hour_of_day' => $value['hour_of_day']
                        ]);
                        foreach (array_keys($value) as $array_key) {
                            $redtrack_report->{$array_key} = $value[$array_key];
                        }
                        $redtrack_report->save();
                    }
                }

                // Domain stats
                $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub6,sub1&sub9=Taboola&tracks_view=true';
                $response = $client->get($url);

                $data = json_decode($response->getBody(), true);
                foreach ($data as $key => $value) {
                    $campaigns = Campaign::where('campaign_id', $value['sub6'])->get();
                    foreach ($campaigns as $index => $campaign) {
                        $value['date'] = $date;
                        $value['user_id'] = $campaign->user_id;
                        $value['provider_id'] = $campaign->provider_id;
                        $value['open_id'] = $campaign->open_id;
                        $value['advertiser_id'] = $campaign->advertiser_id;
                        $redtrack_report = RedtrackDomainStat::firstOrNew([
                            'date' => $date,
                            'campaign_id' => $campaign->id,
                            'sub1' => $value['sub1']
                        ]);
                        foreach (array_keys($value) as $array_key) {
                            $redtrack_report->{$array_key} = $value[$array_key];
                        }
                        $redtrack_report->save();
                    }
                }

                // Content stats
                $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub6,sub7&sub9=Taboola&tracks_view=true';
                $response = $client->get($url);

                $data = json_decode($response->getBody(), true);
                foreach ($data as $key => $value) {
                    $campaigns = Campaign::where('campaign_id', $value['sub6'])->get();
                    foreach ($campaigns as $index => $campaign) {
                        $value['date'] = $date;
                        $value['user_id'] = $campaign->user_id;
                        $value['campaign_id'] = $campaign->id;
                        $value['provider_id'] = $campaign->provider_id;
                        $value['open_id'] = $campaign->open_id;
                        $value['advertiser_id'] = $campaign->advertiser_id;
                        $redtrack_report = RedtrackContentStat::firstOrNew([
                            'date' => $date,
                            'sub7' => $value['sub7']
                        ]);
                        foreach (array_keys($value) as $array_key) {
                            $redtrack_report->{$array_key} = $value[$array_key];
                        }
                        $redtrack_report->save();
                    }
                }
            }
        }
    }

    public function getSummaryDataQuery($data, $campaign = null)
    {
        $summary_data_query = TaboolaReport::select(
            DB::raw('SUM(spent) as total_cost'),
            DB::raw('SUM(conversions_value) as total_revenue'),
            DB::raw('SUM(conversions_value) - SUM(spent) as total_net'),
            DB::raw('(SUM(conversions_value)/SUM(spent)) * 100 as avg_roi')
        );
        $summary_data_query->leftJoin('campaigns', function ($join) use ($data) {
            $join->on('campaigns.campaign_id', '=', 'taboola_reports.campaign_id');
            if ($data['provider']) {
                $join->where('campaigns.provider_id', $data['provider']);
            }
            if ($data['account']) {
                $join->where('campaigns.open_id', $data['account']);
            }
            if ($data['advertiser']) {
                $join->where('campaigns.advertiser_id', $data['advertiser']);
            }
        });
        $summary_data_query->whereBetween('date', [request('start'), request('end')]);

        return $summary_data_query;
    }

    public function getCampaignQuery($data)
    {
        $campaigns_query = Campaign::select([
            DB::raw('MAX(campaigns.id) AS id'),
            DB::raw('campaigns.campaign_id AS campaign_id'),
            DB::raw('MAX(campaigns.name) AS name'),
            DB::raw('MAX(campaigns.status) AS status'),
            DB::raw('MAX(campaigns.budget) AS budget'),
            DB::raw('SUM(impressions) as impressions'),
            DB::raw('SUM(clicks) as clicks'),
            DB::raw('ROUND(SUM(spent), 2) as cost'),
            DB::raw('ROUND(SUM(conversions_value), 2) as total_revenue'),
            DB::raw('ROUND(SUM(conversions_value) - SUM(spent), 2) as profit'),
            DB::raw('ROUND((SUM(conversions_value)/SUM(spent)) * 100, 2) as ROI')
        ]);
        $campaigns_query->leftJoin('taboola_reports', function ($join) use ($data) {
            $join->on('taboola_reports.campaign_id', '=', 'campaigns.id')->whereBetween('taboola_reports.date', [$data['start'], $data['end']]);
        });
        if ($data['provider']) {
            $campaigns_query->where('campaigns.provider_id', $data['provider']);
        }
        if ($data['account']) {
            $campaigns_query->where('campaigns.open_id', $data['account']);
        }
        if ($data['advertiser']) {
            $campaigns_query->where('campaigns.advertiser_id', $data['advertiser']);
        }
        if ($data['search']) {
            $campaigns_query->where('name', 'LIKE', '%' . $data['search'] . '%');
        }
        $campaigns_query->whereIn('campaigns.id', Campaign::select(DB::raw('MAX(campaigns.id) AS id'))->groupBy('campaign_id'));
        $campaigns_query->groupBy('campaigns.id', 'campaigns.campaign_id');

        return $campaigns_query;
    }

    public function getWidgetQuery($campaign, $data)
    {
        //
    }

    public function getContentQuery($campaign, $data)
    {
        $contents_query = Ad::select([
            DB::raw('MAX(ads.id) as id'),
            DB::raw('MAX(ads.campaign_id) as campaign_id'),
            DB::raw('MAX(ads.ad_group_id) as ad_group_id'),
            DB::raw('MAX(ads.ad_id) as ad_id'),
            DB::raw('MAX(ads.name) as name'),
            DB::raw('MAX(ads.status) as status'),
            DB::raw('MAX(ads.image) as image'),
            DB::raw('ROUND(SUM(total_revenue)/SUM(total_conversions), 2) as payout'),
            DB::raw('SUM(clicks) as clicks'),
            DB::raw('SUM(lp_views) as lp_views'),
            DB::raw('SUM(lp_clicks) as lp_clicks'),
            DB::raw('SUM(total_conversions) as total_conversions'),
            DB::raw('SUM(total_conversions) as total_actions'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(clicks)) * 100, 2) as total_actions_cr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(clicks)) * 100, 2) as cr'),
            DB::raw('ROUND(SUM(total_revenue), 2) as total_revenue'),
            DB::raw('ROUND(SUM(cost), 2) as cost'),
            DB::raw('ROUND(SUM(profit), 2) as profit'),
            DB::raw('ROUND((SUM(profit)/SUM(cost)) * 100, 2) as roi'),
            DB::raw('ROUND(SUM(cost)/SUM(clicks), 2) as cpc'),
            DB::raw('ROUND(SUM(cost)/SUM(total_conversions), 2) as cpa'),
            DB::raw('ROUND(SUM(total_revenue)/SUM(clicks), 2) as epc'),
            DB::raw('ROUND((SUM(lp_clicks)/SUM(lp_views)) * 100, 2) as lp_ctr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(lp_views)) * 100, 2) as lp_views_cr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(lp_clicks)) * 100, 2) as lp_clicks_cr'),
            DB::raw('ROUND(SUM(cost)/SUM(lp_clicks), 2) as lp_cpc')
        ]);
        $contents_query->leftJoin('redtrack_content_stats', function ($join) use ($data) {
            $join->on('redtrack_content_stats.sub7', '=', 'ads.ad_id')->whereBetween('redtrack_content_stats.date', [$data['start'], $data['end']]);
        });
        $contents_query->where('ads.campaign_id', $campaign->campaign_id);
        $contents_query->where('name', 'LIKE', '%' . $data['search'] . '%');
        $contents_query->groupBy('ads.ad_id');

        return $contents_query;
    }

    public function getAdGroupQuery($campaign, $data)
    {
        //
    }

    public function getDomainQuery($campaign, $data)
    {
        $domains_query = RedtrackDomainStat::select(
            DB::raw('MAX(id) as id'),
            DB::raw('MAX(sub1) as top_domain'),
            DB::raw('SUM(clicks) as clicks'),
            DB::raw('SUM(lp_views) as lp_views'),
            DB::raw('SUM(lp_clicks) as lp_clicks'),
            DB::raw('SUM(prelp_clicks) as prelp_clicks'),
            DB::raw('ROUND(SUM(lp_clicks) / SUM(lp_views) * 100, 2) as lp_ctr'),
            DB::raw('SUM(conversions) as conversions'),
            DB::raw('ROUND(SUM(conversions) / SUM(clicks) * 100, 2) as cr'),
            DB::raw('SUM(conversions) as total_actions'),
            DB::raw('SUM(conversions) as tr'),
            DB::raw('SUM(revenue) as conversion_revenue'),
            DB::raw('SUM(total_revenue) as total_revenue'),
            DB::raw('SUM(cost) as cost'),
            DB::raw('SUM(profit) as profit'),
            DB::raw('ROUND(SUM(profit) / SUM(cost) * 100, 2) as roi'),
            DB::raw('ROUND(SUM(cost) / SUM(clicks), 2) as cpc'),
            DB::raw('ROUND(SUM(cost) / SUM(total_conversions), 2) as cpa'),
            DB::raw('ROUND(SUM(total_revenue) / SUM(clicks), 2) as epc')
        );
        $domains_query->where('campaign_id', $campaign->id);
        $domains_query->whereBetween('date', [$data['start'], $data['end']]);
        if (request('search')) {
            $domains_query->where('sub1', 'LIKE', '%' . $data['search'] . '%');
        }
        $domains_query->groupBy('sub1');

        return $domains_query;
    }

    public function getPerformanceQuery($campaign, $data)
    {
        //
    }

    public function getPerformanceData($campaign, $time_range)
    {
        return $campaign->taboolaReports()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();
    }

    public function getDomainData($campaign, $time_range)
    {
        return [];
    }

    public function addSiteBlock($campaign, $data)
    {
        $api = new TaboolaAPI(UserProvider::where([
            'provider_id' => $campaign->provider->id,
            'open_id' => $campaign->open_id
        ])->first());

        $api->blockPublisher($campaign->advertiser_id, [
            'sites' => [
                $data['sub1']
            ],
            'patch_operation' => 'ADD'
        ]);
    }

    public function targets(Campaign $campaign)
    {
        //
    }

    public function blockWidgets(Campaign $campaign, $widgets)
    {
        //
    }

    public function unblockWidgets(Campaign $campaign, $widgets)
    {
        //
    }

    public function changeBugget(Campaign $campaign, $data)
    {
        $api = new TaboolaAPI(UserProvider::where([
            'provider_id' => $campaign->provider->id,
            'open_id' => $campaign->open_id
        ])->first());

        $budget = 0;

        if (!isset($data->budgetSetType) || $data->budgetSetType == 1) {
            $budget = $data->budget;
        } else {
            $campaign_data = $api->getCampaign($campaign->advertiser_id, $campaign->campaign_id);

            if ($data->budgetSetType == 2) {
                $budget = $campaign_data['spending_limit'] + ($data->budgetUnit == 1 ? $data->budget : $campaign_data['spending_limit'] * $data->budget / 100);

                if (!empty($data->budgetMax) && $budget > $data->budgetMax) {
                    $budget = $data->budgetMax;
                }
            } else {
                $budget = $campaign_data['spending_limit'] - ($data->budgetUnit == 1 ? $data->budget : $campaign_data['spending_limit'] * $data->budget / 100);

                if (!empty($data->budgetMin) && $budget < $data->budgetMin) {
                    $budget = $data->budgetMin;
                }
            }
        }

        $api->updateCampaign($campaign->advertiser_id, $campaign->campaign_id, [
            'spending_limit' => $budget
        ]);
    }

    public function changeCampaignBid(Campaign $campaign, $data)
    {
        $api = new TaboolaAPI(UserProvider::where([
            'provider_id' => $campaign->provider->id,
            'open_id' => $campaign->open_id
        ])->first());

        $api->updateCampaign($campaign->advertiser_id, $campaign->campaign_id, [
            'cpc' => $data->bid
        ]);
    }

    private function isCampaignGeneration($vendor) {
        if (count($vendor['campaigns']) == 0) {
            return false;
        }

        foreach ($vendor['campaigns'] as $campaign) {
            if (isset($campaign['id'])) {
                return true;
            }
        }

        return false;
    }

    public function storeCampaignVendors($vendor) {
        return $this->isCampaignGeneration($vendor) ? $this->generateAdVendors($vendor) : $this->createCampaignVendors($vendor);
    }

    private function generateAdVendors($vendor) {
        try {
            foreach ($vendor['campaigns'] as $campaign) {
                $campaign_db = Campaign::find($campaign['id']);

                if (!$campaign_db) {
                    continue;
                }

                $api = new TaboolaAPI(UserProvider::where([
                    'provider_id' => $campaign_db->provider->id,
                    'open_id' => $campaign_db->open_id
                ])->first());

                $campaign_data = $api->getCampaign($campaign_db->advertiser_id, $campaign_db->campaign_id);

                foreach (request('contents') as $campaign_item) {
                    $titles = [];

                    $title_creative_set = null;
                    $description_creative_set = null;
                    $image_creative_set = null;
                    $video_creative_set = null;

                    if (isset($campaign_item['titleSet']['id'])) {
                        $title_creative_set = CreativeSet::find($campaign_item['titleSet']['id']);

                        if ($title_creative_set) {
                            $titles = $title_creative_set->titleSets;
                        } else {
                            throw new Exception('No creative set found.');
                        }
                    } else {
                        $titles = $campaign_item['titles'];
                    }

                    $description = '';

                    if (isset($campaign_item['descriptionSet']['id'])) {
                        $description_creative_set = CreativeSet::find($campaign_item['descriptionSet']['id']);

                        if ($description_creative_set) {
                            $description = $description_creative_set->descriptionSets[0]['description'];
                        } else {
                            throw new Exception('No creative set found.');
                        }
                    } else {
                        $description = $campaign_item['description'];
                    }

                    foreach ($titles as $title) {
                        if ($campaign_item['adType'] == 'IMAGE') {
                            $imges = [];

                            if (isset($campaign_item['imageSet']['id'])) {
                                $image_creative_set = CreativeSet::find($campaign_item['imageSet']['id']);

                                if ($image_creative_set) {
                                    $images = $image_creative_set->imageSets;
                                } else {
                                    throw new Exception('No creative set found.');
                                }
                            } else {
                                $images = $campaign_item['images'];
                            }

                            foreach ($images as $image) {
                                $campaign_item_data = $api->createCampaignItem($vendor['selectedAdvertiser'], $campaign_data['id'], $campaign_item['displayUrl']);

                                $db_ad = Ad::firstOrNew([
                                    'ad_id' => $campaign_item_data['id'],
                                    'user_id' => auth()->id(),
                                    'provider_id' => 4,
                                    'campaign_id' => $campaign_data['id'],
                                    'advertiser_id' => $vendor['selectedAdvertiser'],
                                    'ad_group_id' => 'taboola',
                                    'open_id' => $vendor['selectedAccount']
                                ]);

                                $db_ad->name = $title['title'];
                                $db_ad->type = 1;
                                $db_ad->image = Helper::encodeUrl($image_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $image['image']) : $image['image']);
                                $db_ad->status = $campaign_item_data['status'];
                                $db_ad->description = $description;
                                $db_ad->synced = 0;

                                $db_ad->save();

                                $db_ad->creativeSets()->detach();

                                if ($title_creative_set) {
                                    $db_ad->creativeSets()->save($title_creative_set);
                                }

                                if ($description_creative_set) {
                                    $db_ad->creativeSets()->save($description_creative_set);
                                }

                                if ($video_creative_set) {
                                    $db_ad->creativeSets()->save($video_creative_set);
                                }

                                if ($image_creative_set) {
                                    $db_ad->creativeSets()->save($image_creative_set);
                                }
                            }
                        } else {
                            $videos = [];

                            if (isset($campaign_item['videoSet']['id'])) {
                                $video_creative_set = CreativeSet::find($campaign_item['videoSet']['id']);

                                if ($video_creative_set) {
                                    $videos = $video_creative_set->videoSets;
                                } else {
                                    throw new Exception('No creative set found.');
                                }
                            } else {
                                $videos = $campaign_item['videos'];
                            }

                            foreach ($videos as $video) {
                                $campaign_item_data = $api->createCampaignVideoItem($vendor['selectedAdvertiser'], $campaign_data['id'], [
                                    'url' => $campaign_item['displayUrl'],
                                    'title' => $title['title'],
                                    'description' => $description,
                                    'video_url' => Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['video']) : $video['videoUrl']),
                                    'fallback_url' => $video['imageUrl']
                                ]);

                                $db_ad = Ad::firstOrNew([
                                    'ad_id' => $campaign_item_data['id'],
                                    'user_id' => auth()->id(),
                                    'provider_id' => 4,
                                    'campaign_id' => $campaign_data['id'],
                                    'ad_group_id' => 'taboola',
                                    'advertiser_id' => $vendor['selectedAdvertiser'],
                                    'open_id' => $vendor['selectedAccount']
                                ]);

                                $db_ad->name = $title['title'];
                                $db_ad->type = 2;
                                $db_ad->video = Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['video']) : $video['videoUrl']);
                                $db_ad->image = Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['landscape_image']) : $video['imageUrl']);
                                $db_ad->status = $campaign_item_data['status'];
                                $db_ad->description = $description;
                                $db_ad->synced = 1;

                                $db_ad->save();

                                $db_ad->creativeSets()->detach();

                                if ($title_creative_set) {
                                    $db_ad->creativeSets()->save($title_creative_set);
                                }

                                if ($description_creative_set) {
                                    $db_ad->creativeSets()->save($description_creative_set);
                                }

                                if ($video_creative_set) {
                                    $db_ad->creativeSets()->save($video_creative_set);
                                }

                                if ($image_creative_set) {
                                    $db_ad->creativeSets()->save($image_creative_set);
                                }
                            }
                        }
                    }
                }

                $resource_importer = new ResourceImporter();

                $resource_importer->insertOrUpdate('campaigns', [[
                    'campaign_id' => $campaign_data['id'],
                    'provider_id' => 4,
                    'user_id' => auth()->id(),
                    'open_id' => $vendor['selectedAccount'],
                    'advertiser_id' => $vendor['selectedAdvertiser'],
                    'name' => $campaign_data['name'],
                    'status' => $campaign_data['status']
                ]], ['campaign_id', 'provider_id', 'user_id', 'open_id', 'advertiser_id']);

                Helper::pullCampaign();

                event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                    'success' => 1,
                    'vendor' => 'taboola',
                    'vendorName' => 'Taboola'
                ]));

                return $campaign_data;
            }
        } catch (Exception $e) {
            event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                'errors' => [$e->getMessage()],
                'vendor' => 'taboola',
                'vendorName' => 'Taboola'
            ]));

            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    private function createCampaignVendors($vendor) {
        $api = new TaboolaAPI(UserProvider::where([
            'provider_id' => 4,
            'open_id' => $vendor['selectedAccount']
        ])->first());

        try {
            $data = [
                'name' => request('campaignName'),
                'branding_text' => $vendor['campaignBrandText'],
                'cpc' => $vendor['campaignCPC'],
                'spending_limit' => $vendor['campaignSpendingLimit'],
                'spending_limit_model' => $vendor['campaignSpendingLimitModel'],
                'marketing_objective' => $vendor['campaignMarketingObjective'],
                'is_active' => $vendor['campaignIsActive'],
                'start_date' => $vendor['campaignStartDate'],
                'end_date' => $vendor['campaignEndDate'] ?? ''
            ];

            $country_targeting = $vendor['campaignCountryTargeting'] ?? [];
            $platform_targeting = $vendor['campaignPlatformTargeting'] ?? [];

            if (count($country_targeting) > 0) {
                $data['country_targeting'] = [
                    'type' => 'INCLUDE',
                    'value' => $country_targeting
                ];
            }

            if (count($platform_targeting) > 0) {
                $data['platform_targeting'] = [
                    'type' => 'INCLUDE',
                    'value' => $platform_targeting
                ];
            }

            $campaign_data = $api->createCampaign($vendor['selectedAdvertiser'], $data);

            foreach (request('contents') as $campaign_item) {
                $titles = [];

                $title_creative_set = null;
                $description_creative_set = null;
                $image_creative_set = null;
                $video_creative_set = null;

                if (isset($campaign_item['titleSet']['id'])) {
                    $title_creative_set = CreativeSet::find($campaign_item['titleSet']['id']);

                    if ($title_creative_set) {
                        $titles = $title_creative_set->titleSets;
                    } else {
                        throw new Exception('No creative set found.');
                    }
                } else {
                    $titles = $campaign_item['titles'];
                }

                $description = '';

                if (isset($campaign_item['descriptionSet']['id'])) {
                    $description_creative_set = CreativeSet::find($campaign_item['descriptionSet']['id']);

                    if ($description_creative_set) {
                        $description = $description_creative_set->descriptionSets[0]['description'];
                    } else {
                        throw new Exception('No creative set found.');
                    }
                } else {
                    $description = $campaign_item['description'];
                }

                foreach ($titles as $title) {
                    if ($campaign_item['adType'] == 'IMAGE') {
                        $imges = [];

                        if (isset($campaign_item['imageSet']['id'])) {
                            $image_creative_set = CreativeSet::find($campaign_item['imageSet']['id']);

                            if ($image_creative_set) {
                                $images = $image_creative_set->imageSets;
                            } else {
                                throw new Exception('No creative set found.');
                            }
                        } else {
                            $images = $campaign_item['images'];
                        }

                        foreach ($images as $image) {
                            $campaign_item_data = $api->createCampaignItem($vendor['selectedAdvertiser'], $campaign_data['id'], $campaign_item['displayUrl']);

                            $db_ad = Ad::firstOrNew([
                                'ad_id' => $campaign_item_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 4,
                                'campaign_id' => $campaign_data['id'],
                                'advertiser_id' => $vendor['selectedAdvertiser'],
                                'ad_group_id' => 'taboola',
                                'open_id' => $vendor['selectedAccount']
                            ]);

                            $db_ad->name = $title['title'];
                            $db_ad->type = 1;
                            $db_ad->image = Helper::encodeUrl($image_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $image['image']) : $image['image']);
                            $db_ad->status = $campaign_item_data['status'];
                            $db_ad->description = $description;
                            $db_ad->synced = 0;

                            $db_ad->save();

                            $db_ad->creativeSets()->detach();

                            if ($title_creative_set) {
                                $db_ad->creativeSets()->save($title_creative_set);
                            }

                            if ($description_creative_set) {
                                $db_ad->creativeSets()->save($description_creative_set);
                            }

                            if ($video_creative_set) {
                                $db_ad->creativeSets()->save($video_creative_set);
                            }

                            if ($image_creative_set) {
                                $db_ad->creativeSets()->save($image_creative_set);
                            }
                        }
                    } else {
                        $videos = [];

                        if (isset($campaign_item['videoSet']['id'])) {
                            $video_creative_set = CreativeSet::find($campaign_item['videoSet']['id']);

                            if ($video_creative_set) {
                                $videos = $video_creative_set->videoSets;
                            } else {
                                throw new Exception('No creative set found.');
                            }
                        } else {
                            $videos = $campaign_item['videos'];
                        }

                        foreach ($videos as $video) {
                            $campaign_item_data = $api->createCampaignVideoItem($vendor['selectedAdvertiser'], $campaign_data['id'], [
                                'url' => $campaign_item['displayUrl'],
                                'title' => $title['title'],
                                'description' => $description,
                                'video_url' => Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['video']) : $video['videoUrl']),
                                'fallback_url' => $video['imageUrl']
                            ]);

                            $db_ad = Ad::firstOrNew([
                                'ad_id' => $campaign_item_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 4,
                                'campaign_id' => $campaign_data['id'],
                                'ad_group_id' => 'taboola',
                                'advertiser_id' => $vendor['selectedAdvertiser'],
                                'open_id' => $vendor['selectedAccount']
                            ]);

                            $db_ad->name = $title['title'];
                            $db_ad->type = 2;
                            $db_ad->video = Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['video']) : $video['videoUrl']);
                            $db_ad->image = Helper::encodeUrl($video_creative_set ? (env('MIX_APP_URL') . '/storage/images/' . $video['landscape_image']) : $video['imageUrl']);
                            $db_ad->status = $campaign_item_data['status'];
                            $db_ad->description = $description;
                            $db_ad->synced = 1;

                            $db_ad->save();

                            $db_ad->creativeSets()->detach();

                            if ($title_creative_set) {
                                $db_ad->creativeSets()->save($title_creative_set);
                            }

                            if ($description_creative_set) {
                                $db_ad->creativeSets()->save($description_creative_set);
                            }

                            if ($video_creative_set) {
                                $db_ad->creativeSets()->save($video_creative_set);
                            }

                            if ($image_creative_set) {
                                $db_ad->creativeSets()->save($image_creative_set);
                            }
                        }
                    }
                }
            }

            $resource_importer = new ResourceImporter();

            $resource_importer->insertOrUpdate('campaigns', [[
                'campaign_id' => $campaign_data['id'],
                'provider_id' => 4,
                'user_id' => auth()->id(),
                'open_id' => $vendor['selectedAccount'],
                'advertiser_id' => $vendor['selectedAdvertiser'],
                'name' => $campaign_data['name'],
                'status' => $campaign_data['status']
            ]], ['campaign_id', 'provider_id', 'user_id', 'open_id', 'advertiser_id']);

            Helper::pullCampaign();

            event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                'success' => 1,
                'vendor' => 'taboola',
                'vendorName' => 'Taboola'
            ]));

            return $campaign_data;

        } catch (Exception $e) {
            event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                'errors' => [$e->getMessage()],
                'vendor' => 'taboola',
                'vendorName' => 'Taboola'
            ]));

            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }
}
