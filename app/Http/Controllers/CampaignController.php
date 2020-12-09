<?php

namespace App\Http\Controllers;

use App\Endpoints\GeminiAPI;
use App\Endpoints\OutbrainAPI;
use App\Exports\CampaignExport;
use App\Jobs\PullCampaign;
use App\Models\Campaign;
use App\Models\FailedJob;
use App\Models\GeminiDomainPerformanceStat;
use App\Models\GeminiPerformanceStat;
use App\Models\GeminiSitePerformanceStat;
use App\Models\Job;
use App\Models\Provider;
use App\Models\RedtrackDomainStat;
use App\Models\RedtrackReport;
use Carbon\Carbon;
use DB;
use DataTables;
use Exception;

use Maatwebsite\Excel\Facades\Excel;

class CampaignController extends Controller
{
    public function index()
    {
        return view('campaigns.index');
    }

    public function userCampaigns()
    {
        return response()->json([
            'campaigns' => auth()->user()->campaigns
        ]);
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
            DB::raw('conversions as lp_cpc')
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
            $campaigns_query = Campaign::with(['redtrackReport' => function ($q) use ($start, $end) {
                if (request('provider')) {
                    $q->where('provider_id', request('provider'));
                }
                $q->whereBetween('date', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')]);
            }]);
            if (request('provider')) {
                $campaigns_query->where('provider_id', request('provider'));
            }
            if (request('query')) {
                $campaigns_query->where('name', 'LIKE', '%' . request('query') . '%');
            }
            $summary_data_query = RedtrackReport::with(['campaign' => function ($q) {
                if (request('query')) {
                    $q->where('name', 'LIKE', '%' . request('query') . '%');
                }
            }])->select(
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('SUM(profit) as total_net'),
                DB::raw('SUM(roi)/COUNT(*) as avg_roi')
            );
            if (request('provider')) {
                $summary_data_query->where('provider_id', request('provider'));
            }
            $summary_data_query->whereBetween('date', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')]);
        } else {
            $campaigns_query = Campaign::with(['performanceStats' => function ($q) use ($start, $end) {
                $q->whereBetween('day', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')]);
            }]);
            if (request('provider')) {
                $campaigns_query->where('provider_id', request('provider'));
            }
            if (request('query')) {
                $campaigns_query->where('name', 'LIKE', '%' . request('query') . '%');
            }
            $summary_data_query = GeminiPerformanceStat::with(['campaign' => function ($q) {
                if (request('query')) {
                    $q->where('name', 'LIKE', '%' . request('query') . '%');
                }
            }])->select(
                DB::raw('SUM(spend) as total_cost'),
                DB::raw('0 as total_revenue'),
                DB::raw('0 - SUM(spend) as total_net'),
                DB::raw('-100 as avg_roi')
            );
            $summary_data_query->whereBetween('day', [!request('start') ? $start : request('start'), !request('end') ? $end : request('end')]);
        }

        $accounts_query = auth()->user()->providers();
        if (request('provider')) {
            $accounts_query->where('provider_id', request('provider'));
        }

        return response()->json([
            'accounts' => $accounts_query->get(),
            'campaigns' => $campaigns_query->paginate(10),
            'summary_data' => $summary_data_query->first()
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
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            $adVendor = new $adVendorClass;
            $instance = $adVendor->getCampaignInstance($campaign);

            if (isset($instance['id'])) {
                $adVendor->cloneCampaignName($instance);
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
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        $instance = (new $adVendorClass)->getCampaignInstance($campaign);

        if (!isset($instance['id'])) {
            return view('error', [
                'title' => 'There is no compaign was found. Please contact Administrator for this case.'
            ]);
        }

        return view('campaigns.form', compact('instance'));
    }

    public function update(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass)->update($campaign);
    }

    public function status(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass)->status($campaign);
    }

    public function adGroupStatus(Campaign $campaign, $ad_group_id)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass)->adGroupStatus($campaign, $ad_group_id);
    }

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass)->adStatus($campaign, $ad_group_id, $ad_id);
    }

    public function adGroupData(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass)->adGroupData($campaign);
    }

    public function delete(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass)->delete($campaign);
    }

    public function media()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        return (new $adVendorClass)->media();
    }

    public function store()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        return (new $adVendorClass)->store();
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
