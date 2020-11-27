<?php

namespace App\Utils\AdVendors;

use Exception;

use Illuminate\Support\Str;

use App\Models\Provider;
use App\Models\Campaign;

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
        $api = new GeminiAPI($user_provider);

        $campaigns = $api->getCampaigns();

        $this->saveCampaigns($campaign, $user_provider);
        $this->cleanCampaigns($campaign, $user_provider);
    }

    private function saveCampaigns($campaigns, $user_provider)
    {
        foreach ($campaigns as $key => $item) {
            $data = collect($item)->keyBy(function ($value, $key) {
                return Str::of($key)->snake();
            });
            $data['user_id'] = $user_provider->user_id;
            $data['campaign_id'] = $data['id'];
            $data['provider_id'] = $user_provider->provider_id;
            $data['open_id'] = $user_provider->open_id;
            $data['name'] = $data['campaign_name'];
            $data = $data->toArray();
            unset($data['campaign_name']);
            unset($data['id']);
            $campaign = Campaign::firstOrNew(['campaign_id' => $data['campaign_id']]);
            foreach (array_keys($data) as $index => $array_key) {
                $campaign->{$array_key} = $data[$array_key];
            }
            $campaign->save();
        }
    }

    private function cleanCampaigns($campaigns, $user_provider)
    {
        $user_campaigns = $this->user->campaigns->toArray();
        if (count($campaigns) !== count($user_campaigns)) {
            foreach ($user_campaigns as $key => $user_campaign) {
                $should_delete = true;
                foreach ($campaigns as $campaign) {
                    if ($campaign['id'] == $user_campaign['campaign_id']) {
                        $should_delete = false;
                    }
                }
                if ($should_delete) {
                    Campaign::where('campaign_id', $user_campaign['campaign_id'])->first()->delete();
                }
            }
        }
    }
}
