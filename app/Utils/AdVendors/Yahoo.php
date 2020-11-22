<?php

namespace App\Utils\AdVendors;

use Exception;

use App\Models\Provider;
use App\Models\Campaign;

use App\Jobs\PullCampaign;

use App\Endpoints\GeminiAPI;

class Yahoo extends Root
{
    public function api()
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
        $provider = Provider::where('slug', request('provider'))->first();
        $user_info = auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first();
        $api = new GeminiAPI($user_info);

        try {
            $campaign_data = $api->createCampaign();

            $campaign = Campaign::firstOrNew([
                'campaign_id' => $campaign_data['id'],
                'provider_id' => $provider->id,
                'open_id' => $user_info->open_id,
                'user_id' => auth()->id()
            ]);

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

            $campaign->save();
            PullCampaign::dispatch(auth()->user());
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
    }
}
