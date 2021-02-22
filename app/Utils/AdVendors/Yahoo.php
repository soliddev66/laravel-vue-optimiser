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
use App\Models\Provider;
use App\Models\RedtrackContentStat;
use App\Models\RedtrackDomainStat;
use App\Models\RedtrackReport;
use App\Models\UserProvider;
use App\Models\UserTracker;
use App\Models\NetworkSetting;
use App\Vngodev\AdVendorInterface;
use App\Vngodev\Helper;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Yahoo extends Root implements AdVendorInterface
{
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
                    foreach ($content['images'] as $image) {
                        $ads[] = [
                            'adGroupId' => $ad_group_id,
                            'advertiserId' => request('selectedAdvertiser'),
                            'campaignId' => $campaign->campaign_id,
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

    public function pullRedTrack($campaign, $target_date = null)
    {
        $tracker = UserTracker::where('provider_id', $campaign->provider_id)->where('provider_open_id', $campaign->open_id)->first();
        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            if ($target_date) {
                $date = $target_date;
            }
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

                // Domain stats
                $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub1&sub6=' . $campaign->campaign_id . '&tracks_view=true';
                $response = $client->get($url);

                $data = json_decode($response->getBody(), true);

                foreach ($data as $i => $value) {
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

    public function getSummaryDataQuery($data)
    {
        $summary_data_query = Campaign::select(
            DB::raw('SUM(spend) as total_cost'),
            DB::raw('"N/A" as total_revenue'),
            DB::raw('"N/A" as total_net'),
            DB::raw('"N/A" as avg_roi')
        );
        $summary_data_query->leftJoin('gemini_performance_stats', function ($join) use ($data) {
            $join->on('gemini_performance_stats.campaign_id', '=', 'campaigns.campaign_id');
            $join->whereBetween('gemini_performance_stats.day', [$data['start'], $data['end']]);
            $join->whereNotNull('fact_conversion_counting');
        });
        if ($data['provider']) {
            $summary_data_query->where('campaigns.provider_id', $data['provider']);
        }
        if ($data['account']) {
            $summary_data_query->where('campaigns.open_id', $data['account']);
        }
        if ($data['advertiser']) {
            $summary_data_query->where('campaigns.advertiser_id', $data['advertiser']);
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
        $campaigns_query->groupBy('campaigns.campaign_id');

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

    public function targets(Campaign $campaign, $status)
    {
        $api = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

        $attributes = $api->getCampaignAttribute($campaign->campaign_id);

        $result = [];

        $countries = $api->getCountries();

        foreach ($attributes as $attribute) {
            if (($status == 'active' && $attribute['status'] == Campaign::STATUS_ACTIVE)
            || ($status == 'paused' && $attribute['status'] == Campaign::STATUS_PAUSED)) {
                $text = $attribute['type']. ' | ';

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

    public function changeBugget(Campaign $campaign, $budget)
    {
        $api = new GeminiAPI(UserProvider::where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());

        $api->updateCampaignBudget($campaign->campaign_id, $budget);
    }
}
