<?php

namespace App\Http\Controllers;

use App\Jobs\PullCampaign;
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

    private function getCampaign($user_info, $campaign_id)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/campaign/' . $campaign_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true)['response'];
    }

    private function getAdGroups($user_info, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/adgroup?campaignId=' . $campaign_id . '&advertiserid=' . $advertiser_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true)['response'];
    }

    private function getAdsByCampaign($user_info, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/ad?campaignId=' . $campaign_id . '&advertiserid=' . $advertiser_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true)['response'];
    }

    private function getAd($user_info, $ad_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/ad/' . $ad_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true)['response'];
    }

    private function getAds($user_info, $ad_group_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/ad?adGroupId=' . $ad_group_id . '&advertiserid=' . $advertiser_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true)['response'];
    }

    private function getCampaignAttribute($user_info, $campaign_id)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/targetingattribute?pt=CAMPAIGN&pi=' . $campaign_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true)['response'];
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
        $campaigns = Campaign::with(['redtrackReport' => function ($q) use ($end) {
            $q->whereBetween('date', [request('start'), !request('end') ? $end : request('end')]);
        }])->get();

        return response()->json([
            'campaigns' => $campaigns
        ]);
    }

    public function show(Campaign $campaign)
    {
        $user_info = auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first();
        $ad_groups = [];
        $ads = [];
        try {
            $ad_groups = $this->getAdGroups($user_info, $campaign->campaign_id, $campaign->advertiser_id);
            $ads = $this->getAdsByCampaign($user_info, $campaign->campaign_id, $campaign->advertiser_id);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function () use ($campaign, $user_info, &$ad_groups, &$ads) {
                    $ad_groups = $this->getAdGroups($user_info, $campaign->campaign_id, $campaign->advertiser_id);
                    $ads = $this->getAdsByCampaign($user_info, $campaign->campaign_id, $campaign->advertiser_id);
                });
            }
        }

        return view('campaigns.show', compact('campaign', 'ad_groups', 'ads'));
    }

    public function ad(Campaign $campaign, $ad_group_id, $ad_id)
    {
        $ad = [];
        $user_info = auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first();
        try {
            $ad = $this->getAd($user_info, $ad_id, $campaign->advertiser_id);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function () use ($campaign, $user_info, $ad_id, &$ad) {
                    $ad = $this->getAd($user_info, $ad_id, $campaign->advertiser_id);
                });
            }
        }
        $ad['open_id'] = $campaign['open_id'];

        return view('ads.show', compact('ad'));
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

    public function create(Campaign $campaign = null)
    {
        $instance = null;

        if ($campaign) {
            $instance = $this->getInstanceData($campaign);

            if (isset($instance['id'])) {
                $instance['campaignName'] = $instance['campaignName'] . ' - Copy';
            } else {
                $instance = null;
            }
        }

        return view('campaigns.form', compact('instance'));
    }

    public function edit(Campaign $campaign)
    {
        $instance = $this->getInstanceData($campaign);

        if (!isset($instance['id'])) {
            return view('error', [
                'title' => 'There is no compaign was found. Please contact Administrator for this case.'
            ]);
        }

        return view('campaigns.form', compact('instance'));
    }

    private function getInstanceData(Campaign $campaign) {
        $user_info = auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first();
        $instance = [];
        $attributes = [];
        $ad_groups = [];
        $ads = [];

        try {
            $instance = $this->getCampaign($user_info, $campaign->campaign_id);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function() use ($user_info, $campaign, &$instance) {
                    $instance = $this->getCampaign($user_info, $campaign->campaign_id);
                });
            }
        }

        try {
            $attributes = $this->getCampaignAttribute($user_info, $campaign->campaign_id);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function() use ($user_info, $campaign, &$attributes) {
                    $attributes = $this->getCampaignAttribute($user_info, $campaign->campaign_id);
                });
            }
        }

        try {
            $ad_groups = $this->getAdGroups($user_info, $campaign->campaign_id, $campaign->advertiser_id);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function() use ($user_info, $campaign, &$adGroups) {
                    $ad_groups = $this->getAdGroups($user_info, $campaign->campaign_id, $campaign->advertiser_id);
                });
            }
        }

        if (count($ad_groups) > 0) {
            try {
                $ads = $this->getAds($user_info, $ad_groups[0]['id'], $campaign->advertiser_id);
            } catch (Exception $e) {
                if ($e->getCode() == 401) {
                    Token::refresh($user_info, function() use ($user_info, $ad_groups, $campaign, &$ads) {
                        $ads = $this->getAds($user_info, $ad_groups[0]['id'], $campaign->advertiser_id);
                    });
                }
            }
        }

        $instance['instance_id'] = $campaign['id'];
        $instance['open_id'] = $campaign['open_id'];
        $instance['attributes'] = $attributes;
        $instance['adGroups'] = $ad_groups;
        $instance['ads'] = $ads;

        return $instance;
    }

    public function update(Campaign $campaign)
    {
        $data = [];
        $user_info = auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first();

        try {
            $data = $this->updateCampaign($user_info, $campaign);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function() use ($user_info, $campaign, &$data) {
                    $data = $this->updateCampaign($user_info, $campaign);
                });
            } else {
                $data = [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        return $data;
    }

    public function status(Campaign $campaign)
    {
        $data = [];
        $user_info = auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first();

        try {
            $data = $this->updateCampaignStatus($user_info, $campaign);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function() use ($user_info, $campaign, &$data) {
                    $data = $this->updateCampaignStatus($user_info, $campaign);
                });
            } else {
                $data = [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        return $data;
    }

    public function delete(Campaign $campaign)
    {
        $data = [];
        $user_info = auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first();

        try {
            $data = $this->deleteCampaign($user_info, $campaign);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function() use ($user_info, $campaign, &$data) {
                    $data = $this->deleteCampaign($user_info, $campaign);
                });
            } else {
                $data = [
                    'errors' => [$e->getMessage()]
                ];
            }
        }
    }

    private function updateCampaign($user_info, $campaign)
    {
        $client = new Client();
        $campaign_data = $this->updateAdCampaign($client, $user_info, $campaign);
        $ad_group_data = $this->updateAdGroup($client, $user_info, $campaign_data);
        $ad = $this->updateAd($client, $user_info, $campaign_data, $ad_group_data);

        $this->deleteAttributes($client, $user_info);
        $this->createAttributes($client, $user_info, $campaign_data);

        PullCampaign::dispatch(auth()->user());

        return $ad;
    }

    private function updateCampaignStatus($user_info, $campaign)
    {
        $client = new Client();
        $campaign->status = $campaign->status == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;
        $campaign_response = $client->request('PUT', env('BASE_URL') . '/v3/rest/campaign', [
            'body' => json_encode([
                'id' => $campaign->campaign_id,
                'status' => $campaign->status
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        $campaign->save();

        return json_decode($campaign_response->getBody(), true);
    }

    private function deleteCampaign($user_info, $campaign)
    {
        $client = new Client();
        $campaign_response = $client->request('DELETE', env('BASE_URL') . '/v3/rest/campaign/' . $campaign->campaign_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        $campaign->delete();

        return json_decode($campaign_response->getBody(), true);
    }

    private function updateAdCampaign($client, $user_info, $campaign)
    {
        $campaign_response = $client->request('PUT', env('BASE_URL') . '/v3/rest/campaign', [
            'body' => json_encode([
                'id' => $campaign->campaign_id,
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

    private function updateAdGroup($client, $user_info, $campaign_data)
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
        $ad_group_response = $client->request('PUT', env('BASE_URL') . '/v3/rest/adgroup', [
            'body' => json_encode([
                'id' => request('adGroupID'),
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

    private function updateAd($client, $user_info, $campaign_data, $ad_group_data)
    {
        $ad_response = $client->request('PUT', env('BASE_URL') . '/v3/rest/ad', [
            'body' => json_encode([
                'id' => request('adID'),
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

    public function store()
    {
        $data = [];
        $provider = Provider::where('slug', request('provider'))->first();
        $user_info = auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first();
        try {
            $data = $this->createCampaign($provider, $user_info);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function () use ($provider, $user_info, &$data) {
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
        $data = [];
        $client = new Client();
        $campaign_data = $this->createAdCampaign($client, $user_info);

        $campaign = Campaign::firstOrNew([
            'campaign_id' => $campaign_data['response']['id'],
            'provider_id' => $provider->id,
            'open_id' => $user_info->open_id,
            'user_id' => auth()->id()
        ]);

        $campaign->save();

        try {
            $ad_group_data = $this->createAdGroup($client, $user_info, $campaign_data);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function () use ($client, $user_info, $campaign_data, $campaign, &$ad_group_data) {
                    try {
                        $ad_group_data = $this->createAdGroup($client, $user_info, $campaign_data);
                    } catch (Exception $e) {
                        $this->deleteCampaign($user_info, $campaign);
                        return [
                            'errors' => [$e->getMessage()]
                        ];
                    }
                });
            } else {
                $this->deleteCampaign($user_info, $campaign);
                return [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        try {
            $ad = $this->createAd($client, $user_info, $campaign_data, $ad_group_data);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function () use ($client, $user_info, $campaign_data, $campaign, $ad_group_data, &$ad) {
                    try {
                        $ad = $this->createAd($client, $user_info, $campaign_data, $ad_group_data);
                    } catch (Exception $e) {
                        $this->deleteCampaign($user_info, $campaign);
                        $this->deleteAdGroups($user_info, [$ad_group_data['response']['id']]);
                        return [
                            'errors' => [$e->getMessage()]
                        ];
                    }
                });
            } else {
                $this->deleteCampaign($user_info, $campaign);
                $this->deleteAdGroups($user_info, [$ad_group_data['response']['id']]);
                return [
                    'errors' => [$e->getMessage()]
                ];
            }
        }
        try {
            $this->createAttributes($client, $user_info, $campaign_data);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function () use ($client, $user_info, $campaign_data, $campaign) {
                    try {
                        $this->createAttributes($client, $user_info, $campaign_data);
                    } catch (Exception $e) {
                        $this->deleteCampaign($user_info, $campaign);
                        $this->deleteAdGroups($user_info, [$ad_group_data['response']['id']]);
                        $this->deleteAds($user_info, [$ad['response']['id']]);
                        return [
                            'errors' => [$e->getMessage()]
                        ];
                    }
                });
            } else {
                $this->deleteCampaign($user_info, $campaign);
                $this->deleteAdGroups($user_info, [$ad_group_data['response']['id']]);
                $this->deleteAds($user_info, [$ad['response']['id']]);
                return [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        PullCampaign::dispatch(auth()->user());

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

    private function deleteAds($user_info, $ad_ids)
    {
        $client = new Client();
        $data = $client->request('DELETE', env('BASE_URL') . '/v3/rest/ad?id=' . implode('&id=', $ad_ids), [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($data->getBody(), true);
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

    private function deleteAdGroups($user_info, $ad_group_ids)
    {
        $client = new Client();
        $data = $client->request('DELETE', env('BASE_URL') . '/v3/rest/adgroup?id=' . implode('&id=', $ad_group_ids), [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($data->getBody(), true);
    }

    private function deleteAttributes($client, $user_info)
    {
        if (!count(request('dataAttributes'))) {
            return;
        }

        $data = $client->request('DELETE', env('BASE_URL') . '/v3/rest/targetingattribute?id=' . implode('&id=', request('dataAttributes')), [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($data->getBody(), true);
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
                    foreach ($attribute['age'] as $index => $age) {
                        $client->request('POST', env('BASE_URL') . '/v3/rest/targetingattribute', [
                            'body' => json_encode([
                                'advertiserId' => request('selectedAdvertiser'),
                                'type' => 'AGE',
                                'parentType' => 'CAMPAIGN',
                                'parentId' => $campaign_data['response']['id'],
                                'value' => $age,
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
