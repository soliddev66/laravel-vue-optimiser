<?php

namespace App\Http\Controllers;

use App\Endpoints\GeminiAPI;
use App\Exports\CampaignExport;
use App\Models\Ad;
use App\Models\AdGroup;
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
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Maatwebsite\Excel\Facades\Excel;

class CampaignController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            if (request('tracker')) {
                $campaigns_query = Campaign::select(
                    DB::raw('MAX(campaigns.id) as id'),
                    DB::raw('MAX(campaigns.name) as name'),
                    DB::raw('MAX(providers.label) as provider_name'),
                    DB::raw('MAX(campaigns.campaign_id) as campaign_id'),
                    DB::raw('MAX(campaigns.budget) as budget'),
                    DB::raw('MAX(campaigns.status) as status'),
                    DB::raw('ROUND(SUM(total_revenue)/SUM(total_conversions), 2) as payout'),
                    DB::raw('SUM(clicks) as clicks'),
                    DB::raw('SUM(lp_views) as lp_views'),
                    DB::raw('SUM(lp_clicks) as lp_clicks'),
                    DB::raw('SUM(total_conversions) as total_conversions'),
                    DB::raw('SUM(total_conversions) as total_actions'),
                    DB::raw('ROUND((SUM(total_conversions)/SUM(clicks)) * 100, 2) as total_actions_cr'),
                    DB::raw('ROUND((SUM(total_conversions)/SUM(clicks)) * 100, 2) as cr'),
                    DB::raw('ROUND(SUM(total_revenue), 2) as total_revenue'),
                    DB::raw('ROUND(SUM(cost), 2) as cost'),
                    DB::raw('ROUND(SUM(profit), 2) as profit'),
                    DB::raw('ROUND((SUM(profit)/SUM(cost)) * 100, 2) as roi'),
                    DB::raw('ROUND(SUM(cost)/SUM(clicks), 2) as cpc'),
                    DB::raw('ROUND(SUM(cost)/SUM(total_conversions), 2) as cpa'),
                    DB::raw('ROUND(SUM(total_revenue)/SUM(clicks), 2) as epc'),
                    DB::raw('ROUND((SUM(lp_clicks)/SUM(lp_views)) * 100, 2) as lp_ctr'),
                    DB::raw('ROUND((SUM(total_conversions)/SUM(lp_views)) * 100, 2) as lp_views_cr'),
                    DB::raw('ROUND((SUM(total_conversions)/SUM(lp_clicks)) * 100, 2) as lp_clicks_cr'),
                    DB::raw('ROUND(SUM(cost)/SUM(lp_clicks), 2) as lp_cpc')
                )
                ->leftJoin('redtrack_reports', function($join) {
                    $join->on('redtrack_reports.campaign_id', '=', 'campaigns.id')->whereBetween('redtrack_reports.date', [request('start'), request('end')]);
                })
                ->leftJoin('providers', 'providers.id', '=', 'campaigns.provider_id');
                if (request('provider')) {
                    $campaigns_query->where('campaigns.provider_id', request('provider'));
                }
                if (request('account')) {
                    $campaigns_query->where('campaigns.open_id', request('account'));
                }
                if (request('search')) {
                    $campaigns_query->where('name', 'LIKE', '%' . request('search') . '%');
                }
                $campaigns_query->groupBy('campaigns.id');
            } else {
                $campaigns_query = Campaign::with(['performanceStats' => function ($q) {
                    $q->whereBetween('day', [request('start'), request('end')]);
                }]);
                if (request('provider')) {
                    $campaigns_query->where('provider_id', request('provider'));
                }
                if (request('account')) {
                    $campaigns_query->where('open_id', request('account'));
                }
                if (request('search')) {
                    $campaigns_query->where('name', 'LIKE', '%' . request('search') . '%');
                }
            }

            return new DataTableCollectionResource($campaigns_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
        }

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

    public function summary(Campaign $campaign)
    {
        if (request('tracker')) {
            $summary_data_query = RedtrackReport::select(
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('SUM(profit) as total_net'),
                DB::raw('(SUM(profit)/SUM(cost)) * 100 as avg_roi'),
            )
            ->where('campaign_id', $campaign->id)
            ->where('provider_id', $campaign->provider_id)
            ->where('open_id', $campaign->open_id)
            ->whereBetween('date', [request('start'), request('end')]);
        }

        return [
            'summary_data' => $summary_data_query->first()
        ];
    }

    public function widgets(Campaign $campaign)
    {
        $widgets_query = GeminiSitePerformanceStat::select([
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
        ->whereBetween('day', [request('start'), request('end')]);
        if (request('search')) {
            $widgets_query->where(DB::raw('CONCAT(external_site_name, "|", device_type)'), 'LIKE', '%' . request('search') . '%');
        }

        return new DataTableCollectionResource($widgets_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
    }

    public function contents(Campaign $campaign)
    {
        $contents_query = Ad::select([
            DB::raw('MAX(ads.id) as id'),
            DB::raw('MAX(ads.campaign_id) as campaign_id'),
            DB::raw('MAX(ads.ad_group_id) as ad_group_id'),
            DB::raw('MAX(ads.ad_id) as ad_id'),
            DB::raw('MAX(ads.name) as name'),
            DB::raw('MAX(ads.status) as status'),
            DB::raw('ROUND(SUM(total_revenue)/SUM(total_conversions), 2) as payout'),
            DB::raw('SUM(clicks) as clicks'),
            DB::raw('SUM(lp_views) as lp_views'),
            DB::raw('SUM(lp_clicks) as lp_clicks'),
            DB::raw('SUM(total_conversions) as total_conversions'),
            DB::raw('SUM(total_conversions) as total_actions'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(clicks)) * 100, 2) as total_actions_cr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(clicks)) * 100, 2) as cr'),
            DB::raw('ROUND(SUM(total_revenue), 2) as total_revenue'),
            DB::raw('ROUND(SUM(cost), 2) as cost'),
            DB::raw('ROUND(SUM(profit), 2) as profit'),
            DB::raw('ROUND((SUM(profit)/SUM(cost)) * 100, 2) as roi'),
            DB::raw('ROUND(SUM(cost)/SUM(clicks), 2) as cpc'),
            DB::raw('ROUND(SUM(cost)/SUM(total_conversions), 2) as cpa'),
            DB::raw('ROUND(SUM(total_revenue)/SUM(clicks), 2) as epc'),
            DB::raw('ROUND((SUM(lp_clicks)/SUM(lp_views)) * 100, 2) as lp_ctr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(lp_views)) * 100, 2) as lp_views_cr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(lp_clicks)) * 100, 2) as lp_clicks_cr'),
            DB::raw('ROUND(SUM(cost)/SUM(lp_clicks), 2) as lp_cpc')
        ])
        ->leftJoin('redtrack_content_stats', function($join) {
            $join->on('redtrack_content_stats.sub5', '=', 'ads.ad_id')->whereBetween('redtrack_content_stats.date', [request('start'), request('end')]);
        })
        ->where('ads.campaign_id', $campaign->campaign_id);
        if (request('search')) {
            $contents_query->where('name', 'LIKE', '%' . request('search') . '%');
        }
        $contents_query->groupBy('ads.ad_id');

        return new DataTableCollectionResource($contents_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
    }

    public function domains(Campaign $campaign)
    {
        if (request('tracker')) {
            $domains_query = RedtrackDomainStat::select(
                DB::raw('MAX(id) as id'),
                DB::raw('MAX(sub1) as sub1'),
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
            )
            ->where('campaign_id', $campaign->id)
            ->whereBetween('date', [request('start'), request('end')]);
            if (request('search')) {
                $domains_query->where('sub1', 'LIKE', '%' . request('search') . '%');
            }
            $domains_query->groupBy('sub1');
        } else {
            $domains_query = GeminiDomainPerformanceStat::where('campaign_id', $campaign->campaign_id)->whereBetween('day', [request('start'), request('end')]);
            if (request('search')) {
                $domains_query->where('top_domain', 'LIKE', '%' . request('search') . '%');
            }
        }

        return new DataTableCollectionResource($domains_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
    }

    public function adGroups(Campaign $campaign)
    {
        $ad_groups_query = AdGroup::select(
            '*'
        )
        ->where('campaign_id', $campaign->campaign_id);
        if (request('search')) {
            $ad_groups_query->where('name', 'LIKE', '%' . request('search') . '%');
        }

        return new DataTableCollectionResource($ad_groups_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
    }

    public function search()
    {
        if (request('tracker')) {
            $summary_data_query = RedtrackReport::with('campaign')->select(
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('SUM(profit) as total_net'),
                DB::raw('(SUM(profit)/SUM(cost)) * 100 as avg_roi'),
            )->whereBetween('date', [request('start'), request('end')]);
            if (request('provider')) {
                $summary_data_query->where('provider_id', request('provider'));
            }
            if (request('account')) {
                $summary_data_query->where('open_id', request('account'));
            }
        } else {
            // TO-DO: Update this
            $summary_data_query = GeminiPerformanceStat::with('campaign')->select(
                DB::raw('SUM(spend) as total_cost'),
                DB::raw('0 as total_revenue'),
                DB::raw('0 - SUM(spend) as total_net'),
                DB::raw('-100 as avg_roi')
            );
            $summary_data_query->whereBetween('day', [request('start'), request('end')]);
            if (request('provider')) {
                $summary_data_query->where('provider_id', request('provider'));
            }
            if (request('account')) {
                $summary_data_query->where('open_id', request('account'));
            }
        }

        $accounts = [];
        if (request('provider')) {
            $accounts = auth()->user()->providers()->where('provider_id', request('provider'))->get();
        }

        return [
            'accounts' => $accounts,
            'summary_data' => $summary_data_query->first()
        ];
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
            $adVendor = new $adVendorClass();
            $instance = $adVendor->getCampaignInstance($campaign);

            if (isset($instance['id'])) {
                $adVendor->cloneCampaignName($instance);
            } else {
                $instance = null;
            }
        }

        return view('campaigns.form', compact('instance'));
    }

    public function createCampaignAd($campaign_id, $ad_group_id)
    {
        $campaign = Campaign::find($campaign_id);
        if (!$campaign) {
            $campaign = Campaign::where('campaign_id', $campaign_id)->first();
        }

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

        $instance = (new $adVendorClass())->getCampaignInstance($campaign);

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

        return (new $adVendorClass())->update($campaign);
    }

    public function status(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass())->status($campaign);
    }

    public function adGroupStatus($campaign_id, $ad_group_id)
    {
        $campaign = Campaign::find($campaign_id);
        if (!$campaign) {
            $campaign = Campaign::where('campaign_id', $campaign_id)->first();
        }
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass())->adGroupStatus($campaign, $ad_group_id);
    }

    public function adStatus($campaign_id, $ad_group_id, $ad_id)
    {
        $campaign = Campaign::find($campaign_id);
        if (!$campaign) {
            $campaign = Campaign::where('campaign_id', $campaign_id)->first();
        }
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass())->adStatus($campaign, $ad_group_id, $ad_id);
    }

    public function adGroupData(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass())->adGroupData($campaign);
    }

    public function delete(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass())->delete($campaign);
    }

    public function media()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        return (new $adVendorClass())->media();
    }

    public function store()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        return (new $adVendorClass())->store();
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
