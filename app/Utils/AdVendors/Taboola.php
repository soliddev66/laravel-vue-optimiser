<?php

namespace App\Utils\AdVendors;

use App\Endpoints\TaboolaAPI;
use App\Jobs\PullCampaign;
use App\Models\Campaign;
use App\Models\Provider;
use App\Models\RedtrackDomainStat;
use App\Models\RedtrackReport;
use App\Models\UserTracker;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;

class Taboola extends Root
{
    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->first();

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
                'end_date' => request('campaignEndDate'),
                'end_date' => request('campaignEndDate'),
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

            foreach (request('campaignItems') as $campaign_item) {
                $api->createCampaignItem(request('advertiser'), $campaign_data['id'], $campaign_item['url']);
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
                $db_campaign->status = $campaign['status'];
                $db_campaign->advertiser_id = $advertiser['account_id'];

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

    public function update(Campaign $campaign)
    {
        //
    }

    public function getCampaignInstance(Campaign $campaign)
    {
        //
    }

    public function cloneCampaignName(&$instance)
    {
        //
    }

    public function status(Campaign $campaign)
    {
        //
    }

    public function pullAdGroup($user_provider)
    {
        //
    }

    public function pullAd($user_provider)
    {
        //
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
            }
        }
    }
}
