<?php

namespace App\Utils\AdVendors;

use App\Endpoints\OutbrainAPI;
use App\Jobs\PullCampaign;
use App\Models\Campaign;
use App\Models\Provider;
use App\Models\RedtrackReport;
use App\Models\UserTracker;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Outbrain extends Root
{
    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->first();

        return new OutbrainAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first());
    }

    public function languages()
    {
        return config('constants.languages');
    }

    public function countries()
    {
        return $this->api()->getCountries();
    }

    /**
     * @return mixed
     * @throws GuzzleException
     */
    public function advertisers()
    {
        return $this->api()->getMarketers();
    }

    public function store()
    {
        $data = [];
        $api = $this->api();

        try {
            $budget_data = $api->createBudget();
            Log::info('OUTBRAIN: Created budget: ' . $budget_data['id']);

            try {
                $campaign_data = $api->createCampaign($budget_data);
                Log::info('OUTBRAIN: Created campaign: ' . $campaign_data['id']);
            } catch (Exception $e) {
                $api->deleteBudget($budget_data);
                throw $e;
            }

            try {

                $ads = [];

                foreach (request('ads') as $ad) {
                    foreach ($ad['titles'] as $title) {
                        foreach ($ad['images'] as $image) {
                            $ads[] = [
                                'text' => $title['title'],
                                'url' => $ad['targetUrl'],
                                'enabled' => true,
                                'imageMetadata' => [
                                    'url' => $image['url']
                                ]
                            ];
                        }
                    }
                }

                $api->createAds($campaign_data, $ads);
            } catch (Exception $e) {
                $api->deleteCampaign($campaign_data['id']);
                $api->deleteBudget($budget_data);
                throw $e;
            }

            PullCampaign::dispatch(auth()->user());
        } catch (RequestException $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
    }

    public function update(Campaign $campaign)
    {
        $data = [];
        $api = $this->api();

        try {
            $budget_data = $api->updateBudget(request('budgetId'));
            $campaign_data = $api->updateCampaign($campaign->campaign_id);

            $ads = [];
            $updated_ads = [];

            foreach (request('ads') as $content) {
                foreach ($content['titles'] as $title) {
                    foreach ($content['images'] as $image) {
                        $ad = [
                            'text' => $title['title'],
                            'url' => $content['targetUrl'],
                            'enabled' => true,
                            'imageMetadata' => [
                                'url' => $image['url']
                            ]
                        ];

                        if ($title['existing'] && $image['existing']) {
                            $ad['id'] = $content['aId'];

                            $updated_ads[] = $ad;
                        } else {
                            $ads[] = $ad;
                        }
                    }
                }
            }

            if (count($updated_ads) > 0) {
                $ad_data = $api->updateAds($campaign->campaign_id, $updated_ads);
            }

            if (count($ads) > 0) {
                $ad_data = $api->createAds($campaign_data, $ads);
            }

            PullCampaign::dispatch(auth()->user());
        } catch (RequestException $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
    }

    public function getCampaignInstance(Campaign $campaign)
    {
        try {
            $api = new OutbrainAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

            $instance = $api->getCampaign($campaign->campaign_id);

            $instance['provider'] = $campaign->provider->slug;
            $instance['provider_id'] = $campaign['provider_id'];
            $instance['open_id'] = $campaign['open_id'];
            $instance['instance_id'] = $campaign['id'];
            $instance['ads'] = $api->getPromotedLinks($campaign->campaign_id)['promotedLinks'];

            return $instance;
        } catch (Exception $e) {
            return [];
        }
    }

    public function cloneCampaignName(&$instance)
    {
        $instance['name'] = $instance['name'] . ' - Copy';
    }

    public function status(Campaign $campaign)
    {
        try {
            $api = new OutbrainAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());
            $campaign->status = $campaign->status == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;

            $api->updateCampaignStatus($campaign->campaign_id, $campaign->status == Campaign::STATUS_ACTIVE);

            $promoted_links = $api->getPromotedLinks($campaign->campaign_id);

            if ($promoted_links && isset($promoted_links['promotedLinks'])) {
                $promoted_ids = array_column($promoted_links['promotedLinks'], 'id');
                if (count($promoted_ids)) {
                    $api->updatePromotedLinkStatus(implode(',', $promoted_ids), $campaign->status == Campaign::STATUS_ACTIVE);
                }
            }

            $campaign->save();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function pullCampaign($user_provider)
    {
        $api = new OutbrainAPI($user_provider);
        $campaign_ids = [];

        $marketers_response = $api->getMarketers();
        if (array_key_exists('marketers', $marketers_response)) {
            $marketers = $marketers_response['marketers'];
            foreach ($marketers as $key => $marketer) {
                $campaigns_by_marketer = $api->getCampaignsByMarketerId($marketer['id']);
                if (array_key_exists('campaigns', $campaigns_by_marketer)) {
                    $campaigns_by_marketer = $campaigns_by_marketer['campaigns'];
                    foreach ($campaigns_by_marketer as $campaign) {
                        $db_campaign = Campaign::firstOrNew([
                            'campaign_id' => $campaign['id'],
                            'provider_id' => $user_provider->provider_id,
                            'open_id' => $user_provider->open_id,
                            'user_id' => $user_provider->user_id
                        ]);

                        $db_campaign->name = $campaign['name'];
                        $db_campaign->status = $campaign['enabled'] ? 'ACTIVE' : 'PAUSED';
                        $db_campaign->budget = $campaign['budget']['amount'];
                        $db_campaign->advertiser_id = $marketer['id'];
                        $db_campaign->save();
                        $campaign_ids[] = $db_campaign->id;
                    }
                }
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
        //
    }

    public function pullAd($user_provider)
    {
        //
    }

    public function pullRedTrack($campaign)
    {
        $tracker = UserTracker::where('provider_id', $campaign->provider_id)
            ->where('provider_open_id', $campaign->open_id)
            ->first();

        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=hour_of_day&sub5=' . $campaign->campaign_id . '&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);

            foreach ($data as $i => $value) {
                $value['date'] = $date;
                $value['user_id'] = $campaign->user_id;
                $value['campaign_id'] = $campaign->id;
                $value['provider_id'] = $campaign->provider_id;
                $value['open_id'] = $campaign->open_id;
                $redtrack_report = RedtrackReport::firstOrNew([
                    'date' => $date,
                    'sub5' => $campaign->campaign_id,
                    'hour_of_day' => $value['hour_of_day']
                ]);
                foreach (array_keys($value) as $array_key) {
                    $redtrack_report->{$array_key} = $value[$array_key];
                }
                $redtrack_report->save();
            }
        }
    }
}
