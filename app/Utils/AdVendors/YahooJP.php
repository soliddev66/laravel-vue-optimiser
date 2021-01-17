<?php

namespace App\Utils\AdVendors;

use App\Endpoints\GeminiAPI;
use App\Endpoints\YahooJPAPI;
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
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YahooJP extends Root implements AdVendorInterface
{
    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->first();

        return new YahooJPAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first());
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
        //
    }

    public function cloneCampaignName(&$instance)
    {
        //
    }

    public function store()
    {
        //
    }

    public function storeAd(Campaign $campaign, $ad_group_id)
    {
        $api = $this->api();

        try {
            foreach (request('contents') as $content) {
                foreach ($content['images'] as $image) {
                    $file = storage_path('app/public/images/') . $image['image'];
                    $data = file_get_contents($file);
                    $media = $api->createMedia([
                        'accountId' => request('selectedAdvertiser'),
                        'operand' => [[
                            'accountId' => request('selectedAdvertiser'),
                            'imageMedia' => [
                                'data' => base64_encode($data)
                            ],
                            'mediaName' => $image['image'],
                            'mediaTitle' => md5($image['image']),
                            'userStatus' => 'ACTIVE',
                        ]]
                    ]);

                    $media_id = $media['rval']['values'][0]['errors'][0]['details'][0]['requestValue'] ?? $media['rval']['values'][0]['mediaRecord']['mediaId'];

                    if (!$media_id) {
                        throw new Exception(json_encode($media['errors']));
                    }

                    foreach ($content['headlines'] as $headlines) {
                        $ads[] = [
                            'accountId' => request('selectedAdvertiser'),
                            'ad' => [
                                'adType' => 'RESPONSIVE_IMAGE_AD',
                                'responsiveImageAd' => [
                                    'buttonText' => 'FOR_MORE_INFO',
                                    'description' => $content['description'],
                                    'displayUrl' => $content['displayUrl'],
                                    'headline' => $headlines['headline'],
                                    'principal' => $content['principal'],
                                    'url' => $content['targetUrl'],
                                ]
                            ],
                            'adGroupId' => $ad_group_id,
                            'campaignId' => $campaign->campaign_id,
                            'adName' => $headlines['headline'],
                            'mediaId' => $media_id,
                            'userStatus' => 'ACTIVE'
                        ];
                    }
                }
            }

            $ad_data = $api->createAd([
                'accountId' => request('selectedAdvertiser'),
                'operand' => $ads
            ]);

            return $ad_data;
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function update(Campaign $campaign)
    {
        //
    }

    public function delete(Campaign $campaign)
    {
        //
    }

    public function status(Campaign $campaign)
    {
        //
    }

    public function adGroupData(Campaign $campaign)
    {
        //
    }

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id)
    {
        //
    }

    public function adGroupStatus(Campaign $campaign, $ad_group_id)
    {
        //
    }

    public function pullCampaign($user_provider)
    {
        $api = new YahooJPAPI($user_provider);
        $campaign_ids = [];

        $accounts_response = $api->getAccounts();
        $accounts = $accounts_response['rval']['values'];
        foreach ($accounts as $key => $account) {
            $account_id = $account['account']['accountId'];
            $campaigns_by_account_response = $api->getCampaignsByAccountId($account_id);
            $campaigns_by_account = $campaigns_by_account_response['rval']['values'];
            foreach ($campaigns_by_account as $campaign) {
                $campaign = $campaign['campaign'];
                $db_campaign = Campaign::firstOrNew([
                    'campaign_id' => $campaign['campaignId'],
                    'provider_id' => $user_provider->provider_id,
                    'open_id' => $user_provider->open_id,
                    'user_id' => $user_provider->user_id
                ]);

                $db_campaign->name = $campaign['campaignName'];
                $db_campaign->status = $campaign['servingStatus'] === 'SERVING' ? 'ACTIVE' : $campaign['servingStatus'];
                $db_campaign->budget = $campaign['budget']['amount'];
                $db_campaign->advertiser_id = $account_id;
                $db_campaign->save();
                $campaign_ids[] = $db_campaign->id;
            }
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
        Campaign::where('user_id', $user_provider->user_id)->where('provider_id', 5)->chunk(10, function ($campaigns) use ($user_provider, &$ad_group_ids) {
            foreach ($campaigns as $key => $campaign) {
                $ad_groups_response = (new YahooJPAPI($user_provider))->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);
                $ad_groups = $ad_groups_response['rval']['values'];
                foreach ($ad_groups as $key => $ad_group) {
                    $ad_group = $ad_group['adGroup'];
                    $db_ad_group = AdGroup::firstOrNew([
                        'ad_group_id' => $ad_group['adGroupId'],
                        'user_id' => $user_provider->user_id,
                        'provider_id' => $user_provider->provider_id,
                        'campaign_id' => $campaign->campaign_id,
                        'advertiser_id' => $campaign->advertiser_id,
                        'open_id' => $user_provider->open_id
                    ]);

                    $db_ad_group->name = $ad_group['adGroupName'];
                    $db_ad_group->status = $ad_group['userStatus'];
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
        AdGroup::where('user_id', $user_provider->user_id)->where('provider_id', 5)->chunk(10, function ($ad_groups) use ($user_provider, &$ad_ids) {
            foreach ($ad_groups as $key => $ad_group) {
                $ads_response = (new YahooJPAPI($user_provider))->getAds([$ad_group->ad_group_id], $ad_group->advertiser_id);
                $ads = $ads_response['rval']['values'];
                foreach ($ads as $key => $ad) {
                    $ad = $ad['adGroupAd'];
                    $db_ad = Ad::firstOrNew([
                        'ad_id' => $ad['adId'],
                        'user_id' => $user_provider->user_id,
                        'provider_id' => $user_provider->provider_id,
                        'campaign_id' => $ad_group->campaign_id,
                        'advertiser_id' => $ad_group->advertiser_id,
                        'ad_group_id' => $ad_group->ad_group_id,
                        'open_id' => $user_provider->open_id
                    ]);

                    $db_ad->name = $ad['adName'];
                    $db_ad->status = $ad['userStatus'];
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

    public function getSummaryDataQuery($data)
    {
        $summary_data_query = GeminiPerformanceStat::select(
            DB::raw('SUM(spend) as total_cost'),
            DB::raw('"N/A" as total_revenue'),
            DB::raw('"N/A" as total_net'),
            DB::raw('"N/A" as avg_roi')
        );
        $summary_data_query->leftJoin('campaigns', function ($join) use ($data) {
            $join->on('campaigns.campaign_id', '=', 'gemini_performance_stats.campaign_id');
            if ($data['provider']) {
                $join->where('campaigns.provider_id', $data['provider']);
            }
            if ($data['account']) {
                $join->where('campaigns.open_id', $data['account']);
            }
        });
        $summary_data_query->whereBetween('day', [request('start'), request('end')]);

        return $summary_data_query;
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
}
