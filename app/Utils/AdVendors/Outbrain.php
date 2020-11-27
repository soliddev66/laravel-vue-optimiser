<?php

namespace App\Utils\AdVendors;

use Exception;

use App\Endpoints\OutbrainAPI;

use App\Models\Provider;
use App\Endpoints\GeminiAPI;
use GuzzleHttp\Exception\GuzzleException;

use Illuminate\Support\Facades\Log;

use App\Jobs\PullOutbrainCampaign;

class Outbrain extends Root
{
    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        return new OutbrainAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first());
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
                $campaign_data = $api->createAdCampaign($budget_data);
                Log::info('OUTBRAIN: Created campaign: ' . $campaign_data['id']);
            } catch (Exception $e) {
                $api->deleteBudget($budget_data);
                throw $e;
            }

            try {
                $ad_data = $api->createAd($campaign_data);
                Log::info('OUTBRAIN: Created ad: ' . $ad_data['id']);
            } catch (Exception $e) {
                $api->deleteCampaign($campaign_data);
                $api->deleteBudget($budget_data);
                throw $e;
            }

            PullOutbrainCampaign::dispatch(auth()->user());
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
    }
}
