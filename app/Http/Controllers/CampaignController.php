<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\FailedJob;
use App\Models\Job;
use App\Models\Provider;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Token;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with('redtrackReport')->get();

        return view('campaigns.index', compact('campaigns'));
    }

    public function queue()
    {
        $queues = Job::all();
        $failed_queues = FailedJob::all();

        return view('campaigns.queue', compact('queues', 'failed_queues'));
    }

    public function search()
    {
        $end = Carbon::now()->format('Y-m-d');
        $campaigns = Campaign::with(['redtrackReport' => function($q) use ($end) {
            $q->whereBetween('date', [request('start'), !request('end') ? $end : request('end')]);
        }])->get();

        return response()->json([
            'campaigns' => $campaigns
        ]);
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
        $data = [];
        $provider = Provider::where('slug', request('provider'))->first();
        $user_info = auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first();
        try {
            $data = $this->createCampaign($provider, $user_info);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function() use ($provider, $user_info, &$data) {
                    $data = $this->createCampaign($provider, $user_info);
                });
            } else {
                $data = [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        return $data;
    }

    private function createLocationAttribute($client, $location, $campaign_data, $user_info)
    {
        $client->request('POST', env('BASE_URL') . '/v3/rest/targetingattribute', [
            'body' => json_encode([
                'advertiserId' => request('selectedAdvertiser'),
                'type' => 'WOEID',
                'parentType' => 'CAMPAIGN',
                'parentId' => $campaign_data['response']['id'],
                'value' => $location,
                'status' => 'ACTIVE',
                'include' => 'TRUE'
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    private function createCampaign($provider, $user_info)
    {
        $client = new Client();
        $campaign_data = $this->createAdCampaign($client, $user_info);
        $ad_group_data = $this->createAdGroup($client, $user_info, $campaign_data);
        $ad = $this->createAd($client, $user_info, $campaign_data, $ad_group_data);
        $this->createAttributes($client, $user_info, $campaign_data);

        return $ad;
    }

    private function createAd($client, $user_info, $campaign_data, $ad_group_data)
    {
        $ad_response = $client->request('POST', env('BASE_URL') . '/v3/rest/ad', [
            'body' => json_encode([
                'adGroupId' => $ad_group_data['response']['id'],
                'advertiserId' => request('selectedAdvertiser'),
                'campaignId' => $campaign_data['response']['id'],
                'description' => request('description'),
                'displayUrl' => request('displayUrl'),
                'landingUrl' => request('targetUrl'),
                'sponsoredBy' => request('brandname'),
                'imageUrlHQ' => request('imageUrlHQ'),
                'imageUrl' => request('imageUrl'),
                'title' => request('title'),
                'status' => 'ACTIVE'
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($ad_response->getBody(), true);
    }

    private function createAdCampaign($client, $user_info)
    {
        $campaign_response = $client->request('POST', env('BASE_URL') . '/v3/rest/campaign', [
            'body' => json_encode([
                'advertiserId' => request('selectedAdvertiser'),
                'budget' => request('campaignBudget'),
                'budgetType' => request('campaignBudgetType'),
                'campaignName' => request('campaignName'),
                'channel' => request('campaignType'),
                'language' => request('campaignLanguage'),
                'biddingStrategy' => request('campaignStrategy'),
                'conversionRuleConfig' => ['conversionCounting' => request('campaignConversionCounting')],
                'status' => 'ACTIVE'
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($campaign_response->getBody(), true);
    }

    private function createAdGroup($client, $user_info, $campaign_data)
    {
        if (request('campaignType') === 'SEARCH_AND_NATIVE') {
            $bids = [
                [
                    'priceType' => 'CPC',
                    'value' => request('bidAmount'),
                    'channel' => 'SEARCH'
                ],
                [
                    'priceType' => 'CPC',
                    'value' => request('bidAmount'),
                    'channel' => 'NATIVE'
                ]
            ];
        } else {
            $bids = [
                [
                    'priceType' => 'CPC',
                    'value' => request('bidAmount'),
                    'channel' => request('campaignType')
                ]
            ];
        }
        $ad_group_response = $client->request('POST', env('BASE_URL') . '/v3/rest/adgroup', [
            'body' => json_encode([
                'adGroupName' => request('adGroupName'),
                'advertiserId' => request('selectedAdvertiser'),
                'bidSet' => [
                    'bids' => $bids
                ],
                'campaignId' => $campaign_data['response']['id'],
                'biddingStrategy' => request('campaignStrategy'),
                'startDateStr' => request('scheduleType') === 'IMMEDIATELY' ? Carbon::now()->format('Y-m-d') : request('campaignStartDate'),
                'endDateStr' => request('scheduleType') === 'IMMEDIATELY' ? '' : request('campaignEndDate'),
                'status' => 'ACTIVE'
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($ad_group_response->getBody(), true);
    }

    private function createAttributes($client, $user_info, $campaign_data)
    {
        if (count(request('campaignLocation'))) {
            foreach (request('campaignLocation') as $key => $location) {
                $this->createLocationAttribute($client, $location, $campaign_data, $user_info);
            }
        }
        if (count(request('attributes'))) {
            foreach (request('attributes') as $key => $attribute) {
                if ($attribute['gender']) {
                    $client->request('POST', env('BASE_URL') . '/v3/rest/targetingattribute', [
                        'body' => json_encode([
                            'advertiserId' => request('selectedAdvertiser'),
                            'type' => 'GENDER',
                            'parentType' => 'CAMPAIGN',
                            'parentId' => $campaign_data['response']['id'],
                            'value' => $attribute['gender'],
                            'status' => 'ACTIVE',
                            'include' => 'TRUE'
                        ]),
                        'headers' => [
                            'Authorization' => 'Bearer ' . $user_info->token,
                            'Content-Type' => 'application/json'
                        ]
                    ]);
                }
                if ($attribute['age']) {
                    $client->request('POST', env('BASE_URL') . '/v3/rest/targetingattribute', [
                        'body' => json_encode([
                            'advertiserId' => request('selectedAdvertiser'),
                            'type' => 'AGE',
                            'parentType' => 'CAMPAIGN',
                            'parentId' => $campaign_data['response']['id'],
                            'value' => $attribute['age'],
                            'status' => 'ACTIVE',
                            'include' => 'TRUE'
                        ]),
                        'headers' => [
                            'Authorization' => 'Bearer ' . $user_info->token,
                            'Content-Type' => 'application/json'
                        ]
                    ]);
                }

                if ($attribute['device']) {
                    $client->request('POST', env('BASE_URL') . '/v3/rest/targetingattribute', [
                        'body' => json_encode([
                            'advertiserId' => request('selectedAdvertiser'),
                            'type' => 'DEVICE',
                            'parentType' => 'CAMPAIGN',
                            'parentId' => $campaign_data['response']['id'],
                            'value' => $attribute['device'],
                            'status' => 'ACTIVE',
                            'include' => 'TRUE'
                        ]),
                        'headers' => [
                            'Authorization' => 'Bearer ' . $user_info->token,
                            'Content-Type' => 'application/json'
                        ]
                    ]);
                }
            }
        }
    }
}
