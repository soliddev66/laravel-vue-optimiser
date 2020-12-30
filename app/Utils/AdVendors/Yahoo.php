<?php

namespace App\Utils\AdVendors;

use App\Endpoints\GeminiAPI;
use App\Jobs\PullCampaign;
use App\Models\Ad;
use App\Models\AdGroup;
use App\Models\Campaign;
use App\Models\Provider;
use App\Models\RedtrackContentStat;
use App\Models\RedtrackDomainStat;
use App\Models\RedtrackReport;
use App\Models\UserProvider;
use App\Models\UserTracker;
use App\Vngodev\Helper;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Yahoo extends Root
{
    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->first();

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

    public function countries()
    {
        return $this->api()->getCountries();
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

    public function cloneCampaignName(&$instance)
    {
        $instance['campaignName'] = $instance['campaignName'] . ' - Copy';
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

            $ads = [];

            try {
                foreach (request('contents') as $content) {
                    foreach ($content['titles'] as $title) {
                        foreach ($content['images'] as $image) {
                            $ads[] = [
                                'adGroupId' => $ad_group_data['id'],
                                'advertiserId' => request('selectedAdvertiser'),
                                'campaignId' => $campaign_data['id'],
                                'description' => $content['description'],
                                'displayUrl' => $content['displayUrl'],
                                'landingUrl' => $content['targetUrl'],
                                'sponsoredBy' => $content['brandname'],
                                'imageUrlHQ' => Helper::encodeUrl($image['imageUrlHQ']),
                                'imageUrl' => Helper::encodeUrl($image['imageUrl']),
                                'title' => $title['title'],
                                'status' => 'ACTIVE'
                            ];
                        }
                    }
                }

                $ad_data = $api->createAd($ads);
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

            PullCampaign::dispatch(auth()->user());
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }

        return [];
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
                    foreach ($content['images'] as $image) {
                        $ad = [
                            'adGroupId' => $ad_group_data['id'],
                            'advertiserId' => request('selectedAdvertiser'),
                            'campaignId' => $campaign_data['id'],
                            'description' => $content['description'],
                            'displayUrl' => $content['displayUrl'],
                            'landingUrl' => $content['targetUrl'],
                            'sponsoredBy' => $content['brandname'],
                            'imageUrlHQ' => Helper::encodeUrl($image['imageUrlHQ']),
                            'imageUrl' => Helper::encodeUrl($image['imageUrl']),
                            'title' => $title['title'],
                            'status' => 'ACTIVE'
                        ];
                        if ($title['existing'] && $image['existing']) {
                            $ad['id'] = $content['id'];
                            $uupdate_ads[] = $ad;
                        } else {
                            $ads[] = $ad;
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

            PullCampaign::dispatch(auth()->user());
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
            $api = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());
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

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id)
    {
        $api = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());
        $status = request('status') == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;

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

        $campaign_ids = [];
        foreach ($campaigns as $key => $campaign) {
            $db_campaign = Campaign::firstOrNew([
                'campaign_id' => $campaign['id'],
                'provider_id' => $user_provider->provider_id,
                'user_id' => $user_provider->user_id,
                'open_id' => $user_provider->open_id
            ]);

            $db_campaign->name = $campaign['campaignName'];
            $db_campaign->status = $campaign['status'];
            $db_campaign->budget = $campaign['budget'];
            $db_campaign->advertiser_id = $campaign['advertiserId'];
            $db_campaign->save();
            $campaign_ids[] = $db_campaign->id;
        }

        Campaign::where([
            'user_id' => $user_provider->user_id,
            'provider_id' => $user_provider->provider_id,
            'open_id' => $user_provider->open_id
        ])->whereNotIn('id', $campaign_ids)->delete();
    }

    public function pullAdGroup($user_provider)
    {
        $ad_group_ids = [];
        Campaign::where('user_id', $user_provider->user_id)->where('provider_id', 1)->chunk(10, function ($campaigns) use ($user_provider, &$ad_group_ids) {
            foreach ($campaigns as $key => $campaign) {
                $ad_groups = (new GeminiAPI($user_provider))->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);
                foreach ($ad_groups as $key => $ad_group) {
                    $db_ad_group = AdGroup::firstOrNew([
                        'ad_group_id' => $ad_group['id'],
                        'user_id' => $user_provider->user_id,
                        'provider_id' => $user_provider->provider_id,
                        'campaign_id' => $campaign->campaign_id,
                        'advertiser_id' => $campaign->advertiser_id,
                        'open_id' => $user_provider->open_id
                    ]);

                    $db_ad_group->name = $ad_group['adGroupName'];
                    $db_ad_group->status = $ad_group['status'];
                    $db_ad_group->save();
                    $ad_group_ids[] = $db_ad_group->id;
                }
            }
        });

        AdGroup::where([
            'user_id' => $user_provider->user_id,
            'provider_id' => $user_provider->provider_id,
            'open_id' => $user_provider->open_id
        ])->whereNotIn('id', $ad_group_ids)->delete();
    }

    public function pullAd($user_provider)
    {
        $ad_ids = [];
        AdGroup::where('user_id', $user_provider->user_id)->where('provider_id', 1)->chunk(10, function ($ad_groups) use ($user_provider, &$ad_ids) {
            foreach ($ad_groups as $key => $ad_group) {
                $ads = (new GeminiAPI($user_provider))->getAds([$ad_group->ad_group_id], $ad_group->advertiser_id);
                foreach ($ads as $key => $ad) {
                    $db_ad = Ad::firstOrNew([
                        'ad_id' => $ad['id'],
                        'user_id' => $user_provider->user_id,
                        'provider_id' => $user_provider->provider_id,
                        'campaign_id' => $ad_group->campaign_id,
                        'advertiser_id' => $ad_group->advertiser_id,
                        'ad_group_id' => $ad_group->ad_group_id,
                        'open_id' => $user_provider->open_id
                    ]);

                    $db_ad->name = $ad['adName'] ?? $ad['title'];
                    $db_ad->status = $ad['status'];
                    $db_ad->save();
                    $ad_ids[] = $db_ad->id;
                }
            }
        });

        Ad::where([
            'user_id' => $user_provider->user_id,
            'provider_id' => $user_provider->provider_id,
            'open_id' => $user_provider->open_id
        ])->whereNotIn('id', $ad_ids)->delete();
    }

    public function pullRedTrack($campaign)
    {
        $tracker = UserTracker::where('provider_id', $campaign->provider_id)->where('provider_open_id', $campaign->open_id)->first();
        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=hour_of_day&sub6=' . $campaign->campaign_id . '&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);
            if (count($data)) {
                foreach ($data as $i => $value) {
                    $value['date'] = $date;
                    $value['user_id'] = $campaign->user_id;
                    $value['campaign_id'] = $campaign->id;
                    $value['provider_id'] = $campaign->provider_id;
                    $value['open_id'] = $campaign->open_id;
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

                // Domain stats
                $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub1&sub6=' . $campaign->campaign_id . '&tracks_view=true';
                $response = $client->get($url);

                $data = json_decode($response->getBody(), true);

                foreach ($data as $i => $value) {
                    $value['date'] = $date;
                    $value['user_id'] = $campaign->user_id;
                    $value['provider_id'] = $campaign->provider_id;
                    $value['open_id'] = $campaign->open_id;
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

                // Content stats
                $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub5&sub6=' . $campaign->campaign_id . '&tracks_view=true';
                $response = $client->get($url);

                $data = json_decode($response->getBody(), true);

                foreach ($data as $i => $value) {
                    $value['date'] = $date;
                    $value['user_id'] = $campaign->user_id;
                    $value['campaign_id'] = $campaign->id;
                    $value['provider_id'] = $campaign->provider_id;
                    $value['open_id'] = $campaign->open_id;
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
