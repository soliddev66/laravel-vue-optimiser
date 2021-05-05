<?php

namespace App\Utils\AdVendors;

use App\Endpoints\GeminiAPI;
use App\Jobs\PullCampaign;
use App\Models\Ad;
use App\Models\AdGroup;
use App\Models\Campaign;
use App\Models\GeminiDomainPerformanceStat;
use App\Models\GeminiPerformanceStat;
use App\Models\GeminiSitePerformanceStat;
use App\Models\NetworkSetting;
use App\Models\Provider;
use App\Models\RedtrackContentStat;
use App\Models\RedtrackDomainStat;
use App\Models\RedtrackReport;
use App\Models\UserProvider;
use App\Models\UserTracker;
use App\Vngodev\AdVendorInterface;
use App\Vngodev\Helper;
use App\Vngodev\ResourceImporter;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;

class Yahoo extends Root implements AdVendorInterface
{
    use \App\Utils\AdVendors\Attributes\Yahoo;

    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->orWhere('id', request('provider'))->first();

        return new GeminiAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first());
    }

    public function advertisers()
    {
        return $this->api()->getAdvertisers();
    }

    public function signUp()
    {
        return $this->api()->createAdvertiser(request('name'));
    }

    public function languages()
    {
        return $this->api()->getLanguages();
    }

    public function bdsxdSupportedSites()
    {
        $options = [];

        $groups = [];

        $children = [];

        $bdsxdSupportedSites = $this->api()->getBbsxdSupportedSiteGroups();

        foreach ($bdsxdSupportedSites as $bdsxdSupportedSite) {
            if (!isset($groups[$bdsxdSupportedSite['category']])) {
                $children[$bdsxdSupportedSite['category']] = [];
                $groups[$bdsxdSupportedSite['category']] = [
                    'id' => $bdsxdSupportedSite['category'],
                    'type' => 'group',
                    'label' => $bdsxdSupportedSite['category'],
                    'children' => &$children[$bdsxdSupportedSite['category']]
                ];

                $options[] = $groups[$bdsxdSupportedSite['category']];
            }

            $children[$bdsxdSupportedSite['category']][] = [
                'id' => $bdsxdSupportedSite['value'] . '|DESKTOP',
                'type' => 'group',
                'label' => $bdsxdSupportedSite['name'] . ' - Desktop'
            ];

            $children[$bdsxdSupportedSite['category']][] = [
                'id' => $bdsxdSupportedSite['value'] . '|MOBILE',
                'type' => 'group',
                'label' => $bdsxdSupportedSite['name'] . ' - Mobile'
            ];
        }

        $bdsxdSupportedSites = $this->api()->getBbsxdSupportedSites();

        foreach ($bdsxdSupportedSites as $bdsxdSupportedSite) {
            if (!isset($groups[$bdsxdSupportedSite['category']])) {
                $children[$bdsxdSupportedSite['category']] = [];
                $groups[$bdsxdSupportedSite['category']] = [
                    'id' => $bdsxdSupportedSite['category'],
                    'label' => $bdsxdSupportedSite['category'],
                    'type' => 'site',
                    'children' => &$children[$bdsxdSupportedSite['category']]
                ];

                $options[] = $groups[$bdsxdSupportedSite['category']];
            }

            $children[$bdsxdSupportedSite['category']][] = [
                'id' => $bdsxdSupportedSite['value'] . '|DESKTOP',
                'type' => 'site',
                'label' => $bdsxdSupportedSite['name'] . ' - Desktop'
            ];

            $children[$bdsxdSupportedSite['category']][] = [
                'id' => $bdsxdSupportedSite['value'] . '|MOBILE',
                'type' => 'site',
                'label' => $bdsxdSupportedSite['name'] . ' - Mobile'
            ];
        }

        return $options;
    }

    public function countries()
    {
        return $this->api()->getCountries();
    }

    public function networkSetting()
    {
        return NetworkSetting::where('user_id', auth()->id())->get();
    }

    public function storeNetworkSetting()
    {
        NetworkSetting::firstOrNew([
            'name' => request('networkSettingName'),
            'user_id' => auth()->id(),
            'site_block' => request('campaignSiteBlock'),
            'group_1a' => request('campaignSupplyGroup1A'),
            'group_1b' => request('incrementType1b') * request('campaignSupplyGroup1B'),
            'group_2a' => request('incrementType2a') * request('campaignSupplyGroup2A'),
            'group_2b' => request('incrementType2b') * request('campaignSupplyGroup2B'),
            'group_3a' => request('incrementType3a') * request('campaignSupplyGroup3A'),
            'group_3b' => request('incrementType3b') * request('campaignSupplyGroup3B'),
            'site_group' => json_encode(request('supportedSiteCollections'))
        ])->save();

        return [];
    }

    public function getCampaignInstance(Campaign $campaign)
    {
        try {
            $api = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

            $instance = $api->getCampaign($campaign->campaign_id);

            $instance['provider'] = $campaign->provider->slug;
            $instance['provider_id'] = $campaign['provider_id'];
            $instance['open_id'] = $campaign['open_id'];
            $instance['instance_id'] = $campaign['id'];
            $instance['attributes'] = $api->getCampaignAttribute($campaign->campaign_id);
            $instance['adGroups'] = $api->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);

            if (count($instance['adGroups']) > 0) {
                $instance['ads'] = $api->getAds([$instance['adGroups'][0]['id']], $campaign->advertiser_id);
            }

            return $instance;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAdInstance(Campaign $campaign, $ad_group_id, $ad_id)
    {
        try {
            $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

            $ad = $gemini->getAd($ad_id);
            $ad['open_id'] = $campaign['open_id'];

            return $ad;
        } catch (Exception $e) {
            return [];
        }
    }

    public function cloneCampaignName(&$instance)
    {
        $instance['campaignName'] = $instance['campaignName'] . ' - Copy';
    }

    public function cloneAdName(&$instance)
    {
        $instance['title'] = $instance['title'] . ' - Copy';
    }

    public function store()
    {
        $api = $this->api();

        try {
            $campaign_data = $api->createCampaign();

            try {
                $ad_group_data = $api->createAdGroup($campaign_data);
            } catch (Exception $e) {
                $api->deleteCampaign($campaign_data['id']);
                throw $e;
            }

            try {
                foreach (request('contents') as $content) {
                    $titles = [];

                    if (isset($content['titleSet']['id'])) {
                        $titles = CreativeSet::find($content['titleSet']['id'])->titleSets;
                    } else {
                        $titles = $content['titles'];
                    }

                    $description = '';

                    if (isset($content['descriptionSet']['id'])) {
                        $description = CreativeSet::find($content['descriptionSet']['id'])->descriptionSets[0]['description'];
                    } else {
                        $description = $content['description'];
                    }

                    foreach ($titles as $title) {
                        $ad = [
                            'adGroupId' => $ad_group_data['id'],
                            'advertiserId' => request('selectedAdvertiser'),
                            'campaignId' => $campaign_data['id'],
                            'description' => $description,
                            'displayUrl' => $content['displayUrl'],
                            'landingUrl' => $content['targetUrl'],
                            'sponsoredBy' => $content['brandname'],
                            'title' => $title['title'],
                            'status' => 'ACTIVE'
                        ];

                        if ($content['adType'] == 'VIDEO') {
                            $videos = [];

                            if (isset($content['videoSet']['id'])) {
                                $videos = CreativeSet::find($content['videoSet']['id'])->videoSets;
                            } else {
                                $videos = $content['videos'];
                            }

                            foreach ($videos as $video) {
                                if (in_array(request('campaignObjective'), ['INSTALL_APP', 'REENGAGE_APP', 'PROMOTE_BRAND'])) {
                                    $ad['videoPrimaryUrl'] = Helper::encodeUrl(isset($content['videoSet']['id']) ? $video['video'] : $video['videoPrimaryUrl']);
                                } else {
                                    $ad['imagePortraitUrl'] = Helper::encodeUrl(isset($content['videoSet']['id']) ? $video['portrait_image'] : $video['imagePortraitUrl']);
                                    $ad['videoPortraitUrl'] = Helper::encodeUrl(isset($content['videoSet']['id']) ? $video['video'] : $video['videoPortraitUrl']);
                                }
                            }
                        } else {
                            $imges = [];

                            if (isset($content['imageSet']['id'])) {
                                $images = CreativeSet::find($content['imageSet']['id'])->imageSets;
                            } else {
                                $images = $content['images'];
                            }

                            foreach ($images as $image) {
                                $ad['imageUrl'] = Helper::encodeUrl(isset($content['imageSet']['id']) ? (env('MIX_APP_URL') . '/storage/images/' . $image['image']) : $image['imageUrl']);
                                $ad['imageUrlHQ'] = Helper::encodeUrl(isset($content['imageSet']['id']) ? (env('MIX_APP_URL') . ($image['optimiser'] == 0 ? ('/storage/images/' . $image['hq_1200x627_image']) : ('/storage/images/creatives/1200x627/' . $image['hq_image']))) : $image['imageUrlHQ']);
                            }
                        }

                        $ad_data = $api->createAd([$ad]);


                    }
                }

                Helper::pullCampaign();
            } catch (Exception $e) {
                $api->deleteCampaign($campaign_data['id']);
                $api->deleteAdGroups([$ad_group_data['id']]);
                throw $e;
            }

            try {
                $api->createAttributes($campaign_data);
            } catch (Exception $e) {
                $api->deleteCampaign($campaign_data['id']);
                $api->deleteAdGroups([$ad_group_data['id']]);

                $ad_ids = [];

                foreach ($ad_data as $ad) {
                    $ad_ids[] = $ad['id'];
                }
                $api->deleteAds($ad_ids);
                throw $e;
            }
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }

        return [];
    }

    public function storeAd(Campaign $campaign, $ad_group_id)
    {
        $api = $this->api();

        try {
            foreach (request('contents') as $content) {
                foreach ($content['titles'] as $title) {
                    $ad = [
                        'adGroupId' => $ad_group_id,
                        'advertiserId' => request('selectedAdvertiser'),
                        'campaignId' => $campaign->campaign_id,
                        'description' => $content['description'],
                        'displayUrl' => $content['displayUrl'],
                        'landingUrl' => $content['targetUrl'],
                        'sponsoredBy' => $content['brandname'],
                        'title' => $title['title'],
                        'status' => 'ACTIVE'
                    ];

                    if ($content['adType'] == 'VIDEO') {
                        foreach ($content['videos'] as $video) {
                            if (in_array(request('campaignObjective'), ['INSTALL_APP', 'REENGAGE_APP', 'PROMOTE_BRAND'])) {
                                $ad['videoPrimaryUrl'] = Helper::encodeUrl($video['videoPrimaryUrl']);
                            } else {
                                $ad['imagePortraitUrl'] = Helper::encodeUrl($video['imagePortraitUrl']);
                                $ad['videoPortraitUrl'] = Helper::encodeUrl($video['videoPortraitUrl']);
                            }
                        }
                    } else {
                        foreach ($content['images'] as $image) {
                            $ad['imageUrl'] = Helper::encodeUrl($image['imageUrl']);
                            $ad['imageUrlHQ'] = Helper::encodeUrl($image['imageUrlHQ']);
                        }
                    }

                    $ads[] = $ad;
                }
            }

            $ad_data = $api->createAd($ads);

            Helper::pullAd();

            return $ad_data;
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function update(Campaign $campaign)
    {
        try {
            $api = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());
            $campaign_data = $api->updateCampaign($campaign);
            $ad_group_data = $api->updateAdGroup($campaign_data);

            $ads = [];

            $uupdate_ads = [];

            foreach (request('contents') as $content) {
                foreach ($content['titles'] as $title) {
                    $ad = [
                        'adGroupId' => $ad_group_data['id'],
                        'advertiserId' => request('selectedAdvertiser'),
                        'campaignId' => $campaign_data['id'],
                        'description' => $content['description'],
                        'displayUrl' => $content['displayUrl'],
                        'landingUrl' => $content['targetUrl'],
                        'sponsoredBy' => $content['brandname'],
                        'title' => $title['title'],
                        'status' => 'ACTIVE'
                    ];

                    if ($content['adType'] == 'VIDEO') {
                        foreach ($content['videos'] as $video) {
                            if (in_array(request('campaignObjective'), ['INSTALL_APP', 'REENGAGE_APP', 'PROMOTE_BRAND'])) {
                                $ad['videoPrimaryUrl'] = Helper::encodeUrl($video['videoPrimaryUrl']);
                            } else {
                                $ad['imagePortraitUrl'] = Helper::encodeUrl($video['imagePortraitUrl']);
                                $ad['videoPortraitUrl'] = Helper::encodeUrl($video['videoPortraitUrl']);
                            }

                            if ($title['existing'] && $video['existing']) {
                                $ad['id'] = $content['id'];
                                $uupdate_ads[] = $ad;
                            } else {
                                $ads[] = $ad;
                            }
                        }
                    } else {
                        foreach ($content['images'] as $image) {
                            $ad['imageUrl'] = Helper::encodeUrl($image['imageUrl']);
                            $ad['imageUrlHQ'] = Helper::encodeUrl($image['imageUrlHQ']);

                            if ($title['existing'] && $image['existing']) {
                                $ad['id'] = $content['id'];
                                $uupdate_ads[] = $ad;
                            } else {
                                $ads[] = $ad;
                            }
                        }
                    }
                }
            }

            if (count($ads) > 0) {
                $api->createAd($ads);
            }
            $api->updateAd($uupdate_ads);

            $api->deleteAttributes();
            $api->createAttributes($campaign_data);
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }

        return [];
    }

    public function delete(Campaign $campaign)
    {
        try {
            $api = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());
            $api->deleteCampaign($campaign->campaign_id);
            $campaign->delete();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function status(Campaign $campaign)
    {
        $ad_group_body = [];
        $ad_group_ids = [];
        $ad_body = [];

        try {
            $api = new GeminiAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first());
            $campaign->status = $campaign->status == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;

            $api->updateCampaignStatus($campaign);

            $ad_groups = $api->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);

            foreach ($ad_groups as $ad_group) {
                $ad_group_body[] = [
                    'id' => $ad_group['id'],
                    'status' => $campaign->status
                ];
                $ad_group_ids[] = $ad_group['id'];
            }

            $api->updateAdGroups($ad_group_body);

            $ads = $api->getAds($ad_group_ids, $campaign->advertiser_id);

            foreach ($ads as $ad) {
                $ad_body[] = [
                    'adGroupId' => $ad['adGroupId'],
                    'id' => $ad['id'],
                    'status' => $campaign->status
                ];
            }

            $api->updateAds($ad_body);
            $campaign->save();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function adGroupData(Campaign $campaign)
    {
        $start = Carbon::now()->format('Y-m-d');
        $end = Carbon::now()->format('Y-m-d');
        $api = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());
        if (request('tracker')) {
            $summary_data = RedtrackReport::select(
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('SUM(profit) as total_net'),
                DB::raw('SUM(roi)/COUNT(*) as avg_roi')
            )
                ->where('sub6', $campaign->campaign_id)
                ->whereBetween('date', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')])
                ->first();
        } else {
            $summary_data = GeminiPerformanceStat::select(
                DB::raw('SUM(spend) as total_cost'),
                DB::raw('0 as total_revenue'),
                DB::raw('0 - SUM(spend) as total_net'),
                DB::raw('-100 as avg_roi')
            )
                ->where('campaign_id', $campaign->campaign_id)
                ->whereBetween('day', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')])
                ->first();
        }

        return response()->json([
            'ad_groups' => $api->getAdGroups($campaign->campaign_id, $campaign->advertiser_id),
            'summary_data' => $summary_data
        ]);
    }

    public function adGroupSelection(Campaign $campaign)
    {
        $api = new GeminiAPI(auth()->user()->providers()->where([
            'provider_id' => $campaign->provider_id,
            'open_id' => $campaign->open_id
        ])->first());

        $ad_groups = $api->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);

        $result = [];

        if (is_array($ad_groups)) {
            foreach ($ad_groups as $ad_group) {
                $result[] = ['id' => $ad_group['id'], 'text' => $ad_group['adGroupName']];
            }
        }

        return $result;
    }

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id, $status = null)
    {
        $api = new GeminiAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first());

        if ($status == null) {
            $status = request('status') == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;
        }

        try {
            $api->updateAdStatus($ad_group_id, $ad_id, $status);
            $ad = Ad::where('ad_id', $ad_id)->first();
            $ad->status = $status;
            $ad->save();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function adGroupStatus(Campaign $campaign, $ad_group_id)
    {
        $api = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());
        $status = request('status') == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;

        try {
            $ad_group = $api->updateAdGroupStatus($ad_group_id, $status);
            $ads = $api->getAds([$ad_group_id], $campaign->advertiser_id);
            if (count($ads) > 0) {
                $ad_body = [];

                foreach ($ads as $ad) {
                    $ad_body[] = [
                        'adGroupId' => $ad['adGroupId'],
                        'id' => $ad['id'],
                        'status' => $ad_group['status']
                    ];
                }

                $api->updateAds($ad_body);

                foreach ($ads as $key => $ad) {
                    $db_ad = Ad::where('ad_id', $ad['id'])->first();
                    $db_ad->status = $status;
                    $db_ad->save();
                }
            }
            $ad_group = AdGroup::where('ad_group_id', $ad_group_id)->first();
            $ad_group->status = $status;
            $ad_group->save();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function pullCampaign($user_provider)
    {
        $campaigns = (new GeminiAPI($user_provider))->getCampaigns();
        $db_campaigns = [];

        $resource_importer = new ResourceImporter();

        $updated_at = Carbon::now();

        foreach ($campaigns as $key => $campaign) {
            $db_campaigns[] = [
                'campaign_id' => $campaign['id'],
                'provider_id' => $user_provider->provider_id,
                'user_id' => $user_provider->user_id,
                'open_id' => $user_provider->open_id,
                'advertiser_id' => $campaign['advertiserId'],
                'name' => $campaign['campaignName'],
                'status' => $campaign['status'],
                'budget' => $campaign['budget'],
                'updated_at' => $updated_at
            ];
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
        $api = new GeminiAPI($user_provider);
        $db_ad_groups = [];

        $resource_importer = new ResourceImporter();

        $updated_at = Carbon::now();

        Campaign::where('user_id', $user_provider->user_id)->where('provider_id', 1)->chunk(10, function ($campaigns) use ($resource_importer, $api, $user_provider, &$db_ad_groups, $updated_at) {
            foreach ($campaigns as $key => $campaign) {
                $ad_groups = $api->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);
                foreach ($ad_groups as $key => $ad_group) {
                    $db_ad_groups[] = [
                        'ad_group_id' => $ad_group['id'],
                        'user_id' => $user_provider->user_id,
                        'provider_id' => $user_provider->provider_id,
                        'campaign_id' => $campaign->campaign_id,
                        'advertiser_id' => $campaign->advertiser_id,
                        'open_id' => $user_provider->open_id,
                        'name' => $ad_group['adGroupName'],
                        'status' => $ad_group['status'],
                        'updated_at' => $updated_at
                    ];
                }
            }
        });

        if (count($db_ad_groups)) {
            $resource_importer->insertOrUpdate('ad_groups', $db_ad_groups, ['ad_group_id', 'user_id', 'provider_id', 'campaign_id', 'advertiser_id', 'open_id']);

            AdGroup::where([
                'user_id' => $user_provider->user_id,
                'provider_id' => $user_provider->provider_id,
                'open_id' => $user_provider->open_id
            ])->where('updated_at', '<>', $updated_at)->delete();
        }
    }

    public function pullAd($user_provider)
    {
        $api = new GeminiAPI($user_provider);
        $db_ads = [];

        $resource_importer = new ResourceImporter();

        $updated_at = Carbon::now();

        AdGroup::where('user_id', $user_provider->user_id)->where('provider_id', 1)->chunk(10, function ($ad_groups) use ($resource_importer, $api, $user_provider, &$db_ads, $updated_at) {
            foreach ($ad_groups as $key => $ad_group) {
                $ads = $api->getAds([$ad_group->ad_group_id], $ad_group->advertiser_id);
                foreach ($ads as $key => $ad) {
                    $db_ads[] = [
                        'ad_id' => $ad['id'],
                        'user_id' => $user_provider->user_id,
                        'provider_id' => $user_provider->provider_id,
                        'campaign_id' => $ad_group->campaign_id,
                        'advertiser_id' => $ad_group->advertiser_id,
                        'ad_group_id' => $ad_group->ad_group_id,
                        'open_id' => $user_provider->open_id,
                        'name' => $ad['adName'] ?? $ad['title'],
                        'status' => $ad['status'],
                        'image' => !empty($ad['imageUrl']) ? $ad['imageUrl'] : $ad['imagePortraitUrl'],
                        'updated_at' => $updated_at
                    ];
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

    public function pullRedTrack($user_provider, $target_date = null)
    {
        $tracker = UserTracker::where('provider_id', $user_provider->provider_id)->where('provider_open_id', $user_provider->open_id)->first();
        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            if ($target_date) {
                $date = $target_date;
            }
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub6,hour_of_day&sub9=Gemini&tracks_view=true';
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
                $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub6,sub1&sub9=Gemini&tracks_view=true';
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
                $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub6,sub5&sub9=Gemini&tracks_view=true';
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
                            'sub5' => $value['sub5']
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
        $summary_data_query = Campaign::select(
            DB::raw('ROUND(SUM(spend), 2) as total_cost'),
            DB::raw('"N/A" as total_revenue'),
            DB::raw('"N/A" as total_net'),
            DB::raw('"N/A" as avg_roi')
        );
        $summary_data_query->leftJoin('gemini_performance_stats', function ($join) use ($data) {
            $join->on('gemini_performance_stats.campaign_id', '=', 'campaigns.campaign_id');
            $join->whereBetween('gemini_performance_stats.day', [$data['start'], $data['end']]);
            $join->whereNotNull('fact_conversion_counting');
        });
        if (isset($data['provider']) && $data['provider']) {
            $summary_data_query->where('campaigns.provider_id', $data['provider']);
        }
        if (isset($data['account']) && $data['account']) {
            $summary_data_query->where('campaigns.open_id', $data['account']);
        }
        if (isset($data['advertiser']) && $data['advertiser']) {
            $summary_data_query->where('campaigns.advertiser_id', $data['advertiser']);
        }
        if ($campaign) {
            $summary_data_query->where('campaigns.id', $campaign->id);
        }

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
            DB::raw('SUM(clicks) as clicks'),
            DB::raw('ROUND(SUM(spend), 2) as cost'),
            DB::raw('SUM(impressions) as impressions')
        ]);
        $campaigns_query->leftJoin('gemini_performance_stats', function ($join) use ($data) {
            $join->on('gemini_performance_stats.campaign_id', '=', 'campaigns.campaign_id')->whereBetween('gemini_performance_stats.day', [$data['start'], $data['end']])->whereNotNull('fact_conversion_counting');
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
            $campaigns_query->where('campaigns.name', 'LIKE', '%' . $data['search'] . '%');
        }
        $campaigns_query->whereIn('campaigns.id', Campaign::select(DB::raw('MAX(campaigns.id) AS id'))->groupBy('campaign_id'));
        $campaigns_query->groupBy('campaigns.id', 'campaigns.campaign_id');

        return $campaigns_query;
    }

    public function getWidgetQuery($campaign, $data)
    {
        $widgets_query = GeminiSitePerformanceStat::select([
            '*',
            DB::raw('CONCAT(external_site_name, "|", device_type) as widget_id'),
            DB::raw('ROUND(spend / clicks, 2) as calc_cpc'),
            DB::raw('null as tr_conv'),
            DB::raw('null as tr_rev'),
            DB::raw('null as tr_net'),
            DB::raw('null as tr_roi'),
            DB::raw('null as tr_epc'),
            DB::raw('null as epc'),
            DB::raw('null as tr_cpa'),
            DB::raw('clicks as ts_clicks'),
            DB::raw('null as trk_clicks'),
            DB::raw('null as lp_clicks'),
            DB::raw('null as lp_ctr'),
            DB::raw('CONCAT(ROUND(clicks / impressions * 100, 2), "%") as ctr'),
            DB::raw('null as tr_cvr'),
            DB::raw('ROUND(spend / impressions * 1000, 2) as ecpm'),
            DB::raw('null as lp_cr'),
            DB::raw('null as lp_cpc')
        ]);
        $widgets_query->where('campaign_id', $campaign->campaign_id);
        $widgets_query->whereBetween('day', [$data['start'], $data['end']]);
        $widgets_query->where(DB::raw('CONCAT(external_site_name, "|", device_type)'), 'LIKE', '%' . $data['search'] . '%');

        return $widgets_query;
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
            DB::raw('SUM(total_conversions) as conversions'),
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
            $join->on('redtrack_content_stats.sub5', '=', 'ads.ad_id')->whereBetween('redtrack_content_stats.date', [$data['start'], $data['end']]);
        });
        $contents_query->where('ads.campaign_id', $campaign->campaign_id);
        $contents_query->where('ads.open_id', $campaign->open_id);
        $contents_query->where('name', 'LIKE', '%' . $data['search'] . '%');
        $contents_query->groupBy('ads.ad_id');

        return $contents_query;
    }

    public function getAdGroupQuery($campaign, $data)
    {
        $ad_groups_query = AdGroup::select([
            DB::raw('MAX(ad_groups.id) as id'),
            DB::raw('MAX(ad_groups.campaign_id) as campaign_id'),
            DB::raw('MAX(ad_groups.ad_group_id) as ad_group_id'),
            DB::raw('MAX(name) as name'),
            DB::raw('MAX(status) as status'),
            DB::raw('SUM(impressions) as impressions'),
            DB::raw('SUM(clicks) as clicks'),
            DB::raw('ROUND(SUM(spend), 2) as cost')
        ]);
        $ad_groups_query->leftJoin('gemini_performance_stats', function ($join) use ($data) {
            $join->on('gemini_performance_stats.ad_group_id', '=', 'ad_groups.ad_group_id')->whereBetween('gemini_performance_stats.day', [$data['start'], $data['end']])->whereNotNull('fact_conversion_counting');
        });

        $ad_groups_query->where('ad_groups.campaign_id', $campaign->campaign_id);
        $ad_groups_query->where('ad_groups.name', 'LIKE', '%' . $data['search'] . '%');
        $ad_groups_query->groupBy('ad_groups.ad_group_id');

        return $ad_groups_query;
    }

    public function getDomainQuery($campaign, $data)
    {
        $domains_query = GeminiDomainPerformanceStat::select(
            DB::raw('MAX(id) as id'),
            DB::raw('MAX(coalesce(top_domain, package_name)) as top_domain'),
            DB::raw('SUM(clicks) as clicks'),
            DB::raw('SUM(spend) as cost'),
            DB::raw('SUM(impressions) as total_view')
        );
        $domains_query->where('campaign_id', $campaign->campaign_id);
        $domains_query->whereBetween('day', [$data['start'], $data['end']]);
        $domains_query->where('top_domain', 'LIKE', '%' . $data['search'] . '%');
        $domains_query->groupBy('top_domain');

        return $domains_query;
    }

    public function getPerformanceQuery($campaign, $data)
    {
        $performance_query = GeminiPerformanceStat::select(
            'day',
            DB::raw('ROUND(SUM(impressions), 2) as total_impressions'),
            DB::raw('ROUND(SUM(clicks), 2) as total_clicks'),
            DB::raw('ROUND(SUM(spend), 2) as total_cost')
        );
        $performance_query->where('campaign_id', $campaign->campaign_id);
        $performance_query->whereBetween('day', [$data['start'], $data['end']]);
        $performance_query->groupBy('day');

        return $performance_query;
    }

    public function getPerformanceData($campaign, $time_range)
    {
        return $campaign->performanceStats()->whereBetween('day', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();
    }

    public function getDomainData($campaign, $time_range)
    {
        return $campaign->redtrackDomainStats()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();
    }

    public function addSiteBlock($campaign, $data)
    {
        $api = new GeminiAPI(UserProvider::where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());

        $api->addAttributes([
            'advertiserId' => $campaign->advertiser_id,
            'parentType' => 'CAMPAIGN',
            'parentId' => $campaign->campaign_id,
            'type' => 'SITE_BLOCK',
            'exclude' => 'TRUE',
            'value' => $data['sub1'],
            'status' => 'ACTIVE'
        ]);
    }

    public function removeSiteBlock($campaign, $data)
    {
        $api = new GeminiAPI(UserProvider::where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());

        $api->addAttributes([
            'advertiserId' => $campaign->advertiser_id,
            'parentType' => 'CAMPAIGN',
            'parentId' => $campaign->campaign_id,
            'type' => 'SITE_BLOCK',
            'exclude' => 'TRUE',
            'value' => $data['sub1'],
            'status' => 'DELETED'
        ]);
    }

    public function blockSite($campaign, $domain_id)
    {
        $data = $campaign->redtrackDomainStats()->find($domain_id);

        if ($data) {
            $this->addSiteBlock($campaign, $data);
        }

        return [];
    }

    public function unBlockSite($campaign, $domain_id)
    {
        $data = $campaign->redtrackDomainStats()->find($domain_id);

        if ($data) {
            $this->removeSiteBlock($campaign, $data);
        }

        return [];
    }

    public function targets(Campaign $campaign, $status)
    {
        $api = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

        $attributes = $api->getCampaignAttribute($campaign->campaign_id);

        $result = [];

        $countries = $api->getCountries();

        foreach ($attributes as $attribute) {
            if (($status == 'active' && $attribute['status'] == Campaign::STATUS_ACTIVE)
                || ($status == 'paused' && $attribute['status'] == Campaign::STATUS_PAUSED)) {
                $text = $attribute['type'] . ' | ';

                if ($attribute['type'] == 'WOEID') {
                    $text .= $this->getCountryName($countries, $attribute['value']);
                } else {
                    $text .= $attribute['value'];
                }
                $result[] = [
                    'id' => $attribute['id'],
                    'text' => $text
                ];
            }
        }

        return $result;
    }

    public function getCountryName($countries, $id)
    {
        foreach ($countries as $item) {
            if ($id == $item['woeid']) {
                return $item['name'];
            }
        }

        return $id;
    }

    public function blockWidgets(Campaign $campaign, $widgets)
    {
        $api = new GeminiAPI(UserProvider::where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());

        $request_body = [];

        $body = [
            'advertiserId' => $campaign->advertiser_id,
            'parentType' => 'CAMPAIGN',
            'parentId' => $campaign->campaign_id,
            'status' => 'PAUSED'
        ];

        foreach ($widgets as $widget) {
            $request_body[] = $body + ['id' => $widget];
        }

        $api->updateAttributes($request_body);
    }

    public function unblockWidgets(Campaign $campaign, $widgets)
    {
        $api = new GeminiAPI(UserProvider::where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());

        $request_body = [];

        $body = [
            'advertiserId' => $campaign->advertiser_id,
            'parentType' => 'CAMPAIGN',
            'parentId' => $campaign->campaign_id,
            'status' => 'ACTIVE'
        ];

        foreach ($widgets as $widget) {
            $request_body[] = $body + ['id' => $widget];
        }

        $api->updateAttributes($request_body);
    }

    public function changeBugget(Campaign $campaign, $data)
    {
        $api = new GeminiAPI(UserProvider::where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());

        $budget = 0;

        if (!isset($data->budgetSetType) || $data->budgetSetType == 1) {
            $budget = $data->budget;
        } else {
            $campaign_data = $api->getCampaign($campaign->campaign_id);

            if ($data->budgetSetType == 2) {
                $budget = $campaign_data['budget'] + ($data->budgetUnit == 1 ? $data->budget : $campaign_data['budget'] * $data->budget / 100);

                if (!empty($data->budgetMax) && $budget > $data->budgetMax) {
                    $budget = $data->budgetMax;
                }
            } else {
                $budget = $campaign_data['budget'] - ($data->budgetUnit == 1 ? $data->budget : $campaign_data['budget'] * $data->budget / 100);

                if (!empty($data->budgetMin) && $budget < $data->budgetMin) {
                    $budget = $data->budgetMin;
                }
            }
        }

        $api->updateCampaignBudget($campaign->campaign_id, $budget);
    }

    public function changeCampaignBid(Campaign $campaign, $data)
    {
        $api = new GeminiAPI(UserProvider::where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());

        $ad_group_body = [];

        foreach ($data->adGroups as $item) {
            $ad_group = $api->getAdGroup($item->id);

            $bids = $ad_group['bidSet']['bids'];

            for ($i = 0; $i < count($bids); $i++) {
                $bids[$i]['value'] = $item->data->bid;
            }

            $ad_group_body[] = [
                'id' => $ad_group['id'],
                'bidSet' => [
                    'bids' => $bids
                ]
            ];
        }

        $api->updateAdGroups($ad_group_body);
    }
}
