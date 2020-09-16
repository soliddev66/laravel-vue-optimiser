<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Vngodev\Token;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = [];
        foreach (auth()->user()->providers as $key => $provider) {
            try {
                $data = $this->getCampaigns($provider);
                $campaigns += $data['response'];
            } catch (Exception $e) {
                if ($e->getCode() == 401) {
                    $token_client = new Token($provider);
                    $new_token = $token_client->refresh();
                    $provider->token = $new_token['access_token'];
                    $provider->refresh_token = $new_token['refresh_token'];
                    $provider->expires_in = Carbon::now()->addSeconds($new_token['expires_in']);
                    $provider->save();
                    $data = $this->getCampaigns($provider);
                    $campaigns += $data['response'];
                }
            }
        }

        return view('campaigns.index', compact('campaigns'));
    }

    private function getCampaigns($provider)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/campaign', [
            'headers' => [
                'Authorization' => 'Bearer ' . $provider->token,
                'Content-Type' => 'application/json'
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public function create()
    {
        return view('campaigns.create');
    }

    public function store()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        $user_info = auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first();
        try {
            $client = new Client();
            $campaign_response = $client->request('POST', env('BASE_URL') . '/v3/rest/campaign', [
                'body' => json_encode([
                    'advertiserId' => request('selectedAdvertiser'),
                    'budget' => request('campaignBudget'),
                    'budgetType' => request('campaignBudgetType'),
                    'campaignName' => request('campaignName'),
                    'status' => 'PAUSED'
                ]),
                'headers' => [
                    'Authorization' => 'Bearer ' . $user_info->token,
                    'Content-Type' => 'application/json'
                ]
            ]);
            $campaign_data = json_decode($campaign_response->getBody(), true);
            $ad_group_response = $client->request('POST', env('BASE_URL') . '/v3/rest/adgroup', [
                'body' => json_encode([
                    'advertiserId' => request('selectedAdvertiser'),
                    'adGroupName' => request('adGroupName'),
                    'bidSet' => [
                        'bids' => [
                            [
                                'priceType' => request('campaignStrategy'),
                                'value' => request('bidAmount'),
                                'channel' => request('campaignType')
                            ]
                        ]
                    ],
                    'campaignId' => $campaign_data['response']['id'],
                    'startDateStr' => Carbon::now()->format('Y-m-d'),
                    'status' => 'PAUSED'
                ]),
                'headers' => [
                    'Authorization' => 'Bearer ' . $user_info->token,
                    'Content-Type' => 'application/json'
                ]
            ]);

            return json_decode($ad_group_response->getBody(), true);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
