<?php

namespace App\Http\Controllers;

use Exception;

use App\Jobs\PullCampaign;

use App\Models\Job;
use App\Models\Campaign;
use App\Models\Provider;
use App\Models\FailedJob;
use App\Exports\CampaignExport;

use App\Endpoints\GeminiAPI;
use Maatwebsite\Excel\Facades\Excel;

use Carbon\Carbon;

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
        $campaigns = Campaign::with(['redtrackReport' => function ($q) use ($end) {
            $q->whereBetween('date', [request('start'), !request('end') ? $end : request('end')]);
        }])->get();

        return response()->json([
            'campaigns' => $campaigns
        ]);
    }

    public function show(Campaign $campaign)
    {
        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first());

        $ad_groups = $gemini->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);
        $ads = $gemini->getAdsByCampaign($campaign->campaign_id, $campaign->advertiser_id);

        return view('campaigns.show', compact('campaign', 'ad_groups', 'ads'));
    }

    public function ad(Campaign $campaign, $ad_group_id, $ad_id)
    {
        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first());

        $ad = $gemini->getAd($ad_id);
        $ad['open_id'] = $campaign['open_id'];

        return view('ads.show', compact('ad'));
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

        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first());

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
        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first());

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
        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());

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
        $ad_group_body = [];
        $ad_group_ids = [];
        $ad_body = [];
        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());

        try {
            $campaign->status = $campaign->status == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;
            $gemini->updateCampaignStatus($campaign);
            $ad_groups = $gemini->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);

            foreach ($ad_groups as $ad_group) {
                $ad_group_body[] = [
                    'id' => $ad_group['id'],
                    'status' => $campaign->status
                ];
                $ad_group_ids[] = $ad_group['id'];
            }

            $gemini->updateAdGroups($ad_group_body);
            $ads = $gemini->getAds($ad_group_ids, $campaign->advertiser_id);

            foreach ($ads as $ad) {
                $ad_body[] = [
                    'adGroupId' => $ad['adGroupId'],
                    'id' => $ad['id'],
                    'status' => $campaign->status
                ];
            }

            $gemini->updateAds($ad_body);
            $campaign->save();
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
    }

    public function adGroupStatus(Campaign $campaign, $ad_group_id)
    {
        $data = [];
        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());
        $status = request('status') == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;

        try {
            $ad_group = $gemini->updateAdGroupStatus($ad_group_id, $status);
            $ads = $gemini->getAds([$ad_group_id], $campaign->advertiser_id);
            if (count($ads) > 0) {
                $ad_body = [];

                foreach ($ads as $ad) {
                    $ad_body[] = [
                        'adGroupId' => $ad['adGroupId'],
                        'id' => $ad['id'],
                        'status' => $ad_group['status']
                    ];
                }

                $gemini->updateAds($ad_body);
            }
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
    }

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id)
    {
        $data = [];
        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());
        $status = request('status') == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;

        try {
            $gemini->updateAdStatus($ad_group_id, $ad_id, $status);
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
    }

    public function adGroupData(Campaign $campaign)
    {
        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first());

        return response()->json([
            'adGroups' => $gemini->getAdGroups($campaign->campaign_id, $campaign->advertiser_id),
            'ads' => $gemini->getAdsByCampaign($campaign->campaign_id, $campaign->advertiser_id)
        ]);
    }

    public function delete(Campaign $campaign)
    {
        $data = [];
        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first());

        try {
            $gemini->deleteCampaign($campaign);
            $campaign->delete();
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
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

            $campaign->save();
            PullCampaign::dispatch(auth()->user());
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
    }

    public function exportExcel() {
        return Excel::download(new CampaignExport, 'campaigns' . Carbon::now()->format('Y-m-d-H-i-s') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    public function exportCsv() {
        return Excel::download(new CampaignExport, 'campaigns' . Carbon::now()->format('Y-m-d-H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}