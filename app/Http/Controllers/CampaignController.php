<?php

namespace App\Http\Controllers;

use DB;
use Exception;
use DataTables;

use Carbon\Carbon;

use App\Models\Job;
use App\Models\Campaign;
use App\Models\Provider;
use App\Models\FailedJob;
use App\Models\RedtrackReport;
use App\Models\RedtrackDomainStat;
use App\Models\GeminiPerformanceStat;
use App\Models\GeminiSitePerformanceStat;
use App\Models\GeminiDomainPerformanceStat;

use App\Jobs\PullCampaign;
use App\Endpoints\GeminiAPI;
use App\Exports\CampaignExport;

use Maatwebsite\Excel\Facades\Excel;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::all();

        return view('campaigns.index', compact('campaigns'));
    }

    public function queue()
    {
        return view('campaigns.queue');
    }

    public function jobs()
    {
        $jobs = Job::select([
            '*',
            DB::raw('"Pending" as status')
        ]);

        return DataTables::eloquent($jobs)
            ->make();
    }

    public function failedJobs()
    {
        $failed_jobs = FailedJob::select([
            '*',
            DB::raw('"Failed" as status')
        ]);

        return DataTables::eloquent($failed_jobs)
            ->make();
    }

    public function widgets(Campaign $campaign, $start, $end, $tracker = '')
    {
        $widgets = GeminiSitePerformanceStat::select([
            '*',
            DB::raw('CONCAT(external_site_name, "|", device_type) as widget_id'),
            DB::raw('ROUND(spend / clicks, 2) as calc_cpc'),
            DB::raw('conversions as tr_conv'),
            DB::raw('conversions as tr_rev'),
            DB::raw('ROUND(0 - spend, 2) as tr_net'),
            DB::raw('CONCAT(ROUND(((0 - spend) / spend) * 100, 2), "%") as tr_roi'),
            DB::raw('conversions as tr_epc'),
            DB::raw('conversions as epc'),
            DB::raw('conversions as tr_cpa'),
            DB::raw('clicks as ts_clicks'),
            DB::raw('conversions as trk_clicks'),
            DB::raw('conversions as lp_clicks'),
            DB::raw('conversions as lp_ctr'),
            DB::raw('CONCAT(ROUND(clicks / impressions * 100, 2), "%") as ctr'),
            DB::raw('conversions as tr_cvr'),
            DB::raw('ROUND(spend / impressions * 1000, 2) as ecpm'),
            DB::raw('conversions as lp_cr'),
            DB::raw('conversions as lp_cpc'),
        ])
        ->where('campaign_id', $campaign->campaign_id)
        ->whereBetween('day', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')]);

        return DataTables::eloquent($widgets)
            ->addColumn('actions', '-')
            ->make();
    }

    public function domains(Campaign $campaign)
    {
        $start = Carbon::now()->format('Y-m-d');
        $end = Carbon::now()->format('Y-m-d');
        if (request('tracker')) {
            $domains = RedtrackDomainStat::select(
                'sub1',
                DB::raw('SUM(clicks) as clicks'),
                DB::raw('SUM(lp_views) as lp_views'),
                DB::raw('SUM(lp_clicks) as lp_clicks'),
                DB::raw('SUM(prelp_clicks) as prelp_clicks'),
                DB::raw('ROUND(SUM(lp_clicks) / SUM(impressions) * 100, 2) as lp_ctr'),
                DB::raw('SUM(conversions) as conversions'),
                DB::raw('ROUND(SUM(conversions) / SUM(clicks) * 100, 2) as cr'),
                DB::raw('SUM(conversions) as total_actions'),
                DB::raw('SUM(conversions) as tr'),
                DB::raw('SUM(revenue) as conversion_revenue'),
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('SUM(cost) as cost'),
                DB::raw('SUM(profit) as profit'),
                DB::raw('ROUND(SUM(profit) / SUM(cost) * 100, 2) as roi'),
                DB::raw('ROUND(SUM(cost) / SUM(clicks), 2) as cpc'),
                DB::raw('ROUND(SUM(cost) / SUM(total_conversions), 2) as cpa'),
                DB::raw('ROUND(SUM(total_revenue) / SUM(clicks), 2) as epc')
            )->where('campaign_id', $campaign->id)
            ->whereBetween('date', [
                !request('start') ? $start : request('start'),
                !request('end') ? $end : request('end')
            ])
            ->groupBy('sub1')
            ->get();
        } else {
            $domains = GeminiDomainPerformanceStat::where('campaign_id', $campaign->campaign_id)->whereBetween('day', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')])->get();
        }

        return response()->json([
            'domains' => $domains
        ]);
    }

    public function search()
    {
        $start = Carbon::now()->format('Y-m-d');
        $end = Carbon::now()->format('Y-m-d');
        if (request('tracker')) {
            $campaigns = Campaign::with(['redtrackReport' => function ($q) use ($start, $end) {
                if (request('provider')) {
                    $q->where('provider_id', request('provider'));
                }
                $q->whereBetween('date', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')]);
            }])->get();
            $summary_data_query = RedtrackReport::select(
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('SUM(profit) as total_net'),
                DB::raw('SUM(roi)/COUNT(*) as avg_roi')
            );
            if (request('provider')) {
                $summary_data_query->where('provider_id', request('provider'));
            }
            $summary_data_query->whereBetween('date', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')]);
            $summary_data = $summary_data_query->first();
        } else {
            $campaigns = Campaign::with(['performanceStats' => function ($q) use ($start, $end) {
                if (request('provider')) {
                    $q->where('provider_id', request('provider'));
                }
                $q->whereBetween('day', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')]);
            }])->get();
            $summary_data = GeminiPerformanceStat::select(
                DB::raw('SUM(spend) as total_cost'),
                DB::raw('0 as total_revenue'),
                DB::raw('0 - SUM(spend) as total_net'),
                DB::raw('-100 as avg_roi')
            );
            if (request('provider')) {
                $summary_data_query->where('provider_id', request('provider'));
            }
            $summary_data_query->whereBetween('day', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')]);
            $summary_data = $summary_data_query->first();
        }

        return response()->json([
            'campaigns' => $campaigns,
            'summary_data' => $summary_data
        ]);
    }

    public function show(Campaign $campaign)
    {
        return view('campaigns.show', compact('campaign'));
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
        } catch (Exception $e) {
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

    private function getInstanceData(Campaign $campaign)
    {
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
        try {
            $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());
            $campaign->status($gemini, $campaign->status == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE);
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
        $start = Carbon::now()->format('Y-m-d');
        $end = Carbon::now()->format('Y-m-d');
        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first());
        if (request('tracker')) {
            $summary_data = RedtrackReport::select(
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('SUM(profit) as total_net'),
                DB::raw('SUM(roi)/COUNT(*) as avg_roi')
            )
            ->where('sub6', $campaign->campaign_id)
            ->whereBetween('date', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')])
            ->first();
        } else {
            $summary_data = GeminiPerformanceStat::select(
                DB::raw('SUM(spend) as total_cost'),
                DB::raw('0 as total_revenue'),
                DB::raw('0 - SUM(spend) as total_net'),
                DB::raw('-100 as avg_roi')
            )
            ->where('campaign_id', $campaign->campaign_id)
            ->whereBetween('day', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')])
            ->first();
        }

        return response()->json([
            'ad_groups' => $gemini->getAdGroups($campaign->campaign_id, $campaign->advertiser_id),
            'ads' => $gemini->getAdsByCampaign($campaign->campaign_id, $campaign->advertiser_id),
            'summary_data' => $summary_data
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

    public function exportExcel()
    {
        return Excel::download(new CampaignExport(), 'campaigns' . Carbon::now()->format('Y-m-d-H-i-s') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    public function exportCsv()
    {
        return Excel::download(new CampaignExport(), 'campaigns' . Carbon::now()->format('Y-m-d-H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
