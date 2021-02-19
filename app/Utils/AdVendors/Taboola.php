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
use App\Models\UserTracker;
use App\Models\UserProvider;
use App\Vngodev\AdVendorInterface;
use App\Vngodev\Helper;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;

class Taboola extends Root implements AdVendorInterface
{
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
                'end_date' => request('campaignEndDate'),
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

            $db_campaign = Campaign::firstOrNew([
                'campaign_id' => $campaign_data['id'],
                'provider_id' => 4,
                'user_id' => auth()->id(),
                'open_id' => request('account')
            ]);
            $db_campaign->name = $campaign_data['name'];
            $db_campaign->status = $campaign_data['status'];
            $db_campaign->advertiser_id = request('advertiser');

            foreach (request('campaignItems') as $campaign_item) {
                foreach ($campaign_item['titles'] as $title) {
                    foreach ($campaign_item['images'] as $image) {
                        $campaign_item_data = $api->createCampaignItem(request('advertiser'), $campaign_data['id'], $campaign_item['url']);

                        $ad = Ad::firstOrNew([
                            'ad_id' => $campaign_item_data['id'],
                            'user_id' => auth()->id(),
                            'provider_id' => 4,
                            'campaign_id' =>  $campaign_data['id'],
                            'ad_group_id' => 'taboola',
                            'advertiser_id' => request('advertiser'),
                            'open_id' => request('account')
                        ]);

                        $ad->name = $title['title'];
                        $ad->image = $image['image'];
                        $ad->status = $campaign_item_data['status'];
                        $ad->description = $campaign_item['description'];
                        $ad->synced = 0;
                        $ad->save();
                    }
                }
            }

            $db_campaign->save();

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
                'end_date' => request('campaignEndDate'),
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
                foreach ($campaign_item['titles'] as $title) {
                    foreach ($campaign_item['images'] as $image) {
                        $ad = Ad::firstOrNew([
                            'ad_id' => $campaign_item_data['id'],
                            'user_id' => auth()->id(),
                            'provider_id' => 4,
                            'campaign_id' =>  $campaign_data['id'],
                            'ad_group_id' => 'taboola',
                            'advertiser_id' => request('advertiser'),
                            'open_id' => request('account')
                        ]);

                        if ($title['existing'] && $image['existing']) {
                            $campaign_item_data = $api->updateCampaignItem($campaign->advertiser_id, $campaign->campaign_id, $campaign_item['id'], [
                                'url' => $campaign_item['url'],
                                'title' => $title['title'],
                                'description' => $campaign_item['description'],
                                'thumbnail_url' => $image['image']
                            ]);

                            $ad->synced = 1;
                        } else {
                            $campaign_item_data = $api->createCampaignItem(request('advertiser'), $campaign_data['id'], $campaign_item['url']);

                            $ad->synced = 0;
                        }

                        $ad->name = $title['title'];
                        $ad->image = $image['image'];
                        $ad->status = $campaign_item_data['status'];
                        $ad->description = $campaign_item['description'];

                        $ad->save();
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
                    foreach ($campaign_item['images'] as $image) {
                        $campaign_item_data = $api->createCampaignItem($campaign->advertiser_id, $campaign->campaign_id, $campaign_item['url']);
                        $ad = Ad::firstOrNew([
                            'ad_id' => $campaign_item_data['id'],
                            'user_id' => auth()->id(),
                            'provider_id' => 4,
                            'campaign_id' =>  $campaign->campaign_id,
                            'ad_group_id' => 'taboola',
                            'advertiser_id' => $campaign->advertiser_id,
                            'open_id' => request('account')
                        ]);

                        $ad->name = $title['title'];
                        $ad->image = $image['image'];
                        $ad->status = $campaign_item_data['status'];
                        $ad->description = $campaign_item['description'];
                        $ad->synced = 0;
                        $ad->save();

                        $ad->url = $campaign_item['url'];

                        $ads[] = $ad;
                    }
                }
            }

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

                        $ad = Ad::firstOrNew([
                            'ad_id' => $campaign_item_data['id'],
                            'user_id' => auth()->id(),
                            'provider_id' => 4,
                            'campaign_id' =>  $campaign->campaign_id,
                            'ad_group_id' => 'taboola',
                            'advertiser_id' => $campaign->advertiser_id,
                            'open_id' => request('account')
                        ]);

                        $ad->name = $title['title'];
                        $ad->image = $image['image'];
                        $ad->status = $campaign_item_data['status'];
                        $ad->description = $campaign_item['description'];
                        $ad->synced = $synced;

                        $ad->save();

                        $ad->url = $campaign_item['url'];

                        $ads[] = $ad;
                    }
                }
            }

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
        $advertisers = $api->getAdvertisers()['results'];

        $campaign_ids = [];

        foreach ($advertisers as $advertiser) {
            $campaigns = $api->getCampaigns($advertiser['account_id'])['results'];

            foreach ($campaigns as $campaign) {
                $db_campaign = Campaign::firstOrNew([
                    'campaign_id' => $campaign['id'],
                    'provider_id' => $user_provider->provider_id,
                    'user_id' => $user_provider->user_id,
                    'open_id' => $user_provider->open_id
                ]);
                $db_campaign->name = $campaign['name'];
                $db_campaign->status = $campaign['status'] === 'RUNNING' ? 'ACTIVE' : $campaign['status'];
                $db_campaign->advertiser_id = $advertiser['account_id'];
                $db_campaign->budget = $campaign['spending_limit'];

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
            $instance['items'] = $api->getCampaignItems($campaign->advertiser_id, $campaign->campaign_id)['results'];

            foreach ($instance['items'] as &$item) {
                $ad = Ad::where('ad_id', $item['id'])->first();

                if ($ad) {
                    if (empty($item['title'])) {
                        $item['title'] = $ad['title'];
                    }
                    if (empty($item['description'])) {
                        $item['description'] = $ad['description'];
                    }
                    if (empty($item['thumbnail_url'])) {
                        $item['thumbnail_url'] = $ad['image'];
                    }
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

    public function pullAdGroup($user_provider)
    {
        //
    }

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id, $status = null)
    {
        //
    }

    public function pullAd($user_provider)
    {
        $ad_ids = [];

        Campaign::where('user_id', $user_provider->user_id)->where('provider_id', $user_provider->provider_id)->chunk(10, function ($campaigns) use ($user_provider, &$ad_ids) {
            $api = new TaboolaAPI($user_provider);
            foreach ($campaigns as $key => $campaign) {
                $campaign_items = $api->getCampaignItems($campaign->advertiser_id, $campaign->campaign_id)['results'];

                if ($campaign_items && count($campaign_items)) {
                    foreach ($campaign_items as $campaign_item) {
                        $ad = Ad::firstOrNew([
                            'ad_id' => $campaign_item['id'],
                            'user_id' => $user_provider->user_id,
                            'provider_id' => $user_provider->provider_id,
                            'campaign_id' => $campaign->campaign_id,
                            'ad_group_id' => 'taboola',
                            'advertiser_id' => $campaign->advertiser_id,
                            'open_id' => $user_provider->open_id
                        ]);

                        $ad->name = $campaign_item['title'] ?? 'NA';
                        $ad->status = $campaign_item['status'] === 'RUNNING' ? 'ACTIVE' : $campaign_item['status'];
                        $ad->save();
                        $ad_ids[] = $ad->id;
                    }
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
                $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub7&sub6=' . $campaign->campaign_id . '&tracks_view=true';
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

    public function getSummaryDataQuery($data)
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
        $campaigns_query->groupBy('campaigns.campaign_id');

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

    public function targets(Campaign $campaign)
    {

    }
}
