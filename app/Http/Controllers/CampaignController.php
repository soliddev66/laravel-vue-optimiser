<?php

namespace App\Http\Controllers;

use Token;
use Exception;

use App\Jobs\PullCampaign;

use App\Models\Job;
use App\Models\Campaign;
use App\Models\Provider;
use App\Models\FailedJob;

use App\Endpoints\GeminiAPI;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with('redtrackReport')->get();

        return view('campaigns.index', compact('campaigns'));
    }

    private function getAdGroup($user_info, $ad_group_id)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/adgroup/' . $ad_group_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
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
        $gemini = new GeminiAPI($user_info);

        $ad_groups = $gemini->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);
        $ads = $gemini->getAdsByCampaign($campaign->campaign_id, $campaign->advertiser_id);

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

    public function createCampaignAd(Campaign $campaign, $ad_group_id)
    {
        return view('campaigns.adForm', compact('campaign', 'ad_group_id'));
    }

    public function storeAd(Campaign $campaign, $ad_group_id)
    {
        $data = [];
        $user_info = auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first();

        $gemini = new GeminiAPI($user_info);

        try {
            $campaign_data = $gemini->getCampaign($campaign->campaign_id);
            $ad_group = $gemini->getAdGroup($ad_group_id);
            $data = $gemini->createAd($campaign_data, $ad_group);
        }  catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
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
        $gemini = new GeminiAPI($user_info);

        $instance = $gemini->getCampaign($campaign->campaign_id);

        $instance['open_id'] = $campaign['open_id'];
        $instance['instance_id'] = $campaign['id'];

        $instance['attributes'] = $gemini->getCampaignAttribute($campaign->campaign_id);

        $instance['adGroups'] = $gemini->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);

        if (count($instance['adGroups']) > 0) {
            $instance['ads'] = $gemini->getAds([$instance['adGroups'][0]['id']], $campaign->advertiser_id);
        }

        return $instance;
    }

    public function update(Campaign $campaign)
    {
        $data = [];
        $user_info = auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first();
        $gemini = new GeminiAPI($user_info);

        try {
            $campaign_data = $gemini->updateAdCampaign($campaign);
            $ad_group_data = $gemini->updateAdGroup($campaign_data);
            $ad = $gemini->updateAd($campaign_data, $ad_group_data);

            $gemini->deleteAttributes();
            $gemini->createAttributes($campaign_data);

            PullCampaign::dispatch(auth()->user());
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
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

    public function adGroupStatus(Campaign $campaign, $ad_group_id)
    {
        $data = [];
        $user_info = auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first();

        try {
            $ad_group = $this->updateAdGroupStatus($user_info, $ad_group_id, request('status'));
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function() use ($user_info, $ad_group_id, &$data) {
                    $ad_group = $this->updateAdGroupStatus($user_info, $ad_group_id, request('status'));
                });
            } else {
                $data = [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        if (!isset($data['errors'])) {
            try {
                $ads = $this->getAds($user_info, [$ad_group_id], $campaign->advertiser_id);
            } catch (Exception $e) {
                if ($e->getCode() == 401) {
                    Token::refresh($user_info, function() use ($user_info, $ad_group_id, $campaign, &$ad) {
                        $ads = $this->getAds($user_info, [$ad_group_id], $campaign->advertiser_id);
                    });
                } else {
                    $data = [
                        'errors' => [$e->getMessage()]
                    ];
                }
            }

            if (isset($ads) && count($ads) > 0) {
                foreach ($ads as $ad) {
                    $ad_request_body[] = [
                        'adGroupId' => $ad['adGroupId'],
                        'id' => $ad['id'],
                        'status' => $ad_group['response']['status']
                    ];
                }
            }

            try {
                $this->updateAdsStatus($user_info, $ad_request_body);
            } catch (Exception $e) {
                if ($e->getCode() == 401) {
                    Token::refresh($user_info, function() use ($user_info, $ad_request_body) {
                        $this->updateAdsStatus($user_info, $ad_request_body);
                    });
                } else {
                    $data = [
                        'errors' => [$e->getMessage()]
                    ];
                }
            }
        }

        return $data;
    }

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id)
    {
        $data = [];
        $user_info = auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first();

        try {
            $data = $this->updateAdStatus($user_info, $ad_group_id, $ad_id, request('status'));
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($user_info, function() use ($user_info, $ad_group_id, $ad_id, &$data) {
                    $data = $this->updateAdStatus($user_info, $ad_id, request('status'));
                });
            } else {
                $data = [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        return $data;
    }

    public function adGroupData(Campaign $campaign)
    {
        $user_info = auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first();
        $gemini = new GeminiAPI($user_info);

        return response()->json([
            'adGroups' => $gemini->getAdGroups($campaign->campaign_id, $campaign->advertiser_id),
            'ads' => $gemini->getAdsByCampaign($campaign->campaign_id, $campaign->advertiser_id)
        ]);
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

        $ad_group_body = [];
        $ad_group_request_ids = [];
        $ad_request_ids = [];

        $gemini = new GeminiAPI($user_info);

        $ad_groups = $gemini->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);

        foreach ($ad_groups as $ad_group) {
            $ad_group_body[] = [
                'id' => $ad_group['id'],
                'status' => $campaign->status
            ];
            $ad_group_request_ids[] = $ad_group['id'];
        }
        $this->updateAdGroupsStatus($user_info, $ad_group_body);

        $ads = $this->getAds($user_info, $ad_group_request_ids, $campaign->advertiser_id);

        foreach ($ads as $ad) {
            $ad_request_ids[] = [
                'adGroupId' => $ad['adGroupId'],
                'id' => $ad['id'],
                'status' => $campaign->status
            ];
        }
        $this->updateAdsStatus($user_info, $ad_request_ids);

        $campaign->save();

        return json_decode($campaign_response->getBody(), true);
    }

    private function updateAdGroupsStatus($user_info, $body)
    {
        $client = new Client();
        $ad_group_response = $client->request('PUT', env('BASE_URL') . '/v3/rest/adgroup', [
            'body' => json_encode($body),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);
        return json_decode($ad_group_response->getBody(), true);
    }

    private function updateAdsStatus($user_info, $body)
    {
        $client = new Client();
        $ad_response = $client->request('PUT', env('BASE_URL') . '/v3/rest/ad', [
            'body' => json_encode($body),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($ad_response->getBody(), true);
    }


    private function updateAdGroupStatus($user_info, $ad_group_id, $status)
    {
        $client = new Client();
        $ad_group_response = $client->request('PUT', env('BASE_URL') . '/v3/rest/adgroup', [
            'body' => json_encode([
                'id' => $ad_group_id,
                'status' => $status == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($ad_group_response->getBody(), true);
    }

    private function updateAdStatus($user_info, $ad_group_id, $ad_id, $ad_status)
    {
        $client = new Client();
        $ad_response = $client->request('PUT', env('BASE_URL') . '/v3/rest/ad', [
            'body' => json_encode([
                'adGroupId' => $ad_group_id,
                'id' => $ad_id,
                'status' => $ad_status == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE
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
        $gemini = new GeminiAPI($user_info);

        try {
            $campaign_data = $gemini->createAdCampaign();

            $campaign = Campaign::firstOrNew([
                'campaign_id' => $campaign_data['id'],
                'provider_id' => $provider->id,
                'open_id' => $user_info->open_id,
                'user_id' => auth()->id()
            ]);

            $campaign->save();

            try {
                $ad_group_data = $gemini->createAdGroup($campaign_data);
            } catch (Exception $e) {
                $gemini->deleteCampaign($campaign);
                throw $e;
            }

            try {
                $ad = $gemini->createAd($campaign_data, $ad_group_data);
            } catch (Exception $e) {
                $gemini->deleteCampaign($campaign);
                $gemini->deleteAdGroups([$ad_group_data['id']]);
                throw $e;
            }

            try {
                $gemini->createAttributes($campaign_data);
            } catch (Exception $e) {
                $gemini->deleteCampaign($campaign);
                $gemini->deleteAdGroups([$ad_group_data['id']]);
                $gemini->deleteAds([$ad['id']]);
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
}
