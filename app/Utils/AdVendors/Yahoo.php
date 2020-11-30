<?php

namespace App\Utils\AdVendors;

use Exception;

use Carbon\Carbon;
use GuzzleHttp\Client;

use Illuminate\Support\Str;

use App\Models\Provider;
use App\Models\Campaign;

use App\Models\UserTracker;
use App\Models\RedtrackReport;
use App\Models\RedtrackDomainStat;

use App\Jobs\PullCampaign;

use App\Endpoints\GeminiAPI;

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

    public function store()
    {
        $data = [];
        $api = $this->api();

        try {
            $campaign_data = $api->createCampaign();

            try {
                $ad_group_data = $api->createAdGroup($campaign_data);
            } catch (Exception $e) {
                $api->deleteCampaign($campaign);
                throw $e;
            }

            try {
                $ad = $api->createAd($campaign_data, $ad_group_data);
            } catch (Exception $e) {
                $api->deleteCampaign($campaign);
                $api->deleteAdGroups([$ad_group_data['id']]);
                throw $e;
            }

            try {
                $api->createAttributes($campaign_data);
            } catch (Exception $e) {
                $api->deleteCampaign($campaign);
                $api->deleteAdGroups([$ad_group_data['id']]);
                $api->deleteAds([$ad['id']]);
                throw $e;
            }

            PullCampaign::dispatch(auth()->user());
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
    }

    public function pullCampaign($user_provider)
    {
        $campaigns = (new GeminiAPI($user_provider))->getCampaigns();

        $campaign_ids = [];
        foreach ($campaigns as $key => $item) {
            $data = collect($item)->keyBy(function ($value, $key) {
                return Str::of($key)->snake();
            })->toArray();

            $data['name'] = $data['campaign_name'];

            $campaign = Campaign::firstOrNew([
                'campaign_id' => $data['id'],
                'provider_id' => $user_provider->provider_id,
                'user_id' => $user_provider->user_id,
                'open_id' => $user_provider->open_id
            ]);

            unset($data['campaign_name']);
            unset($data['id']);

            foreach (array_keys($data) as $index => $array_key) {
                $campaign->{$array_key} = $data[$array_key];
            }
            $campaign->save();
            $campaign_ids[] = $campaign->id;
        }

        Campaign::where([
            'user_id' => $user_provider->user_id,
            'provider_id' => $user_provider->provider_id,
            'open_id' => $user_provider->open_id
        ])->whereNotIn('id', $campaign_ids)->delete();
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

            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub1&sub6=' . $campaign->campaign_id . '&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);

            foreach ($data as $i => $value) {
                $value['date'] = $date;
                $value['user_id'] = $campaign->user_id;
                $value['campaign_id'] = $campaign->id;
                $value['provider_id'] = $campaign->provider_id;
                $value['open_id'] = $campaign->open_id;
                $redtrack_report = RedtrackDomainStat::firstOrNew([
                    'date' => $date,
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
