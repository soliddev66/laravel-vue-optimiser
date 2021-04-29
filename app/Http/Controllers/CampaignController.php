<?php

namespace App\Http\Controllers;

use App\Endpoints\GeminiAPI;
use App\Exports\CampaignExport;
use App\Models\Ad;
use App\Models\AdGroup;
use App\Models\Campaign;
use App\Models\FailedJob;
use App\Models\Provider;
use App\Models\RedtrackDomainStat;
use App\Models\RedtrackReport;
use App\Models\Rule;
use App\Models\UserProvider;
use App\Vngodev\Helper;
use Carbon\Carbon;
use DB;
use DataTables;
use Illuminate\Support\Facades\Queue;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Maatwebsite\Excel\Facades\Excel;
use Redis;

class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('campaigns.index');
    }

    public function userCampaigns()
    {
        return response()->json([
            'campaigns' => auth()->user()->campaigns()->select(DB::raw('MAX(id) as id'), 'campaign_id', 'provider_id', DB::raw('MAX(name) as name'))->groupBy('campaign_id')->groupBy('provider_id')->get()
        ]);
    }

    public function queue()
    {
        return view('campaigns.queue');
    }

    public function jobs()
    {
        $jobs = Redis::lrange('queues:default', 0, -1);

        return response()->json($jobs);
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
                DB::raw('ROUND(SUM(cost), 2) as total_cost'),
                DB::raw('ROUND(SUM(total_revenue), 2) as total_revenue'),
                DB::raw('ROUND(SUM(profit), 2) as total_net'),
                DB::raw('ROUND((SUM(profit)/SUM(cost)) * 100, 2) as avg_roi'),
            );
            $summary_data_query->where('campaign_id', $campaign->id);
            $summary_data_query->where('provider_id', $campaign->provider_id);
            $summary_data_query->where('open_id', $campaign->open_id);
            $summary_data_query->whereBetween('date', [request('start'), request('end')]);
        } else {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            $summary_data_query = (new $adVendorClass())->getSummaryDataQuery(request()->all(), $campaign);
        }

        return [
            'summary_data' => $summary_data_query->first()
        ];
    }

    public function targets(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass)->targets($campaign, request('status'));
    }

    public function widgets(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
        $widgets_query = (new $adVendorClass())->getWidgetQuery($campaign, request()->all());

        return new DataTableCollectionResource($widgets_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
    }

    public function publishers(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
        $widgets_query = (new $adVendorClass())->getPublisherQuery($campaign, request()->all());

        return new DataTableCollectionResource($widgets_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
    }

    public function contents(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
        $contents_query = (new $adVendorClass())->getContentQuery($campaign, request()->all());

        return new DataTableCollectionResource($contents_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
    }

    public function adGroups(Campaign $campaign)
    {
        if (request('tracker')) {
            $ad_groups_query = AdGroup::select(
                '*',
                DB::raw('NULL as impressions'),
                DB::raw('NULL as clicks'),
                DB::raw('NULL as cost')
            );
            $ad_groups_query->where('campaign_id', $campaign->campaign_id);
            $ad_groups_query->where('open_id', $campaign->open_id);
            $ad_groups_query->where('name', 'LIKE', '%' . request('search') . '%');
        } else {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            $ad_groups_query = (new $adVendorClass())->getAdGroupQuery($campaign, request()->all());
        }

        return new DataTableCollectionResource($ad_groups_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
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
                DB::raw('ROUND(SUM(lp_clicks) / SUM(lp_views) * 100, 2) as lp_ctr'),
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
            );
            $domains_query->where('campaign_id', $campaign->id);
            $domains_query->whereBetween('date', [request('start'), request('end')]);
            if (request('search')) {
                $domains_query->where('sub1', 'LIKE', '%' . request('search') . '%');
            }
            $domains_query->groupBy('sub1');
        } else {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            $domains_query = (new $adVendorClass())->getDomainQuery($campaign, request()->all());
        }

        return new DataTableCollectionResource($domains_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
    }

    public function rules(Campaign $campaign)
    {
        $rules_query = Rule::select(
            DB::raw('rules.id as id'),
            DB::raw('rules.name as name'),
            DB::raw('rule_rule_actions.rule_action_id as rule_action_id'),
            DB::raw('rule_actions.name as action_name'),
            DB::raw('rules.id as clicks'),
            DB::raw('rules.status as status')
        )->join('rule_rule_actions', 'rules.id', 'rule_rule_actions.rule_id')
        ->join('rule_actions', 'rule_rule_actions.rule_action_id', 'rule_actions.id');
        $rules_query->where(DB::raw('JSON_EXTRACT(rule_rule_actions.action_data, "$.ruleCampaigns")'), 'REGEXP', '\\b' . $campaign->id . '\\b');
        if (request('search')) {
            $rules_query->where('rules.name', 'LIKE', '%' . request('search') . '%')->orWhere('rule_actions.name', 'LIKE', '%' . request('search') . '%');
        }

        return new DataTableCollectionResource($rules_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
    }

    public function performance(Campaign $campaign)
    {
        if (request('tracker')) {
            $performance_query = RedtrackReport::select(
                'date',
                DB::raw('ROUND(SUM(cost), 2) as total_cost'),
                DB::raw('ROUND(SUM(profit), 2) as total_net'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('ROUND(SUM(cost)/SUM(total_conversions), 2) as cpa'),
                DB::raw('ROUND(SUM(total_revenue), 2) as total_revenue'),
                DB::raw('ROUND((SUM(profit)/SUM(cost)) * 100, 2) as roi'),
                DB::raw('ROUND(SUM(total_conversions), 2) as total_conversions'),
                DB::raw('ROUND(SUM(total_revenue)/SUM(clicks), 2) as epc')
            )
            ->where('campaign_id', $campaign->id)
            ->whereBetween('date', [request('start'), request('end')]);
            $performance_query->groupBy('date');
        } else {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            $performance_query = (new $adVendorClass())->getPerformanceQuery($campaign, request()->all());
        }

        return $performance_query->get();
    }

    public function data()
    {
        if (request('tracker')) {
            $campaigns_query = Campaign::select(
                DB::raw('MAX(campaigns.id) as id'),
                DB::raw('MAX(campaigns.name) as name'),
                DB::raw('MAX(providers.label) as provider_name'),
                DB::raw('MAX(providers.icon) as provider_icon'),
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
            );
            $campaigns_query->leftJoin('redtrack_reports', function ($join) {
                $join->on('redtrack_reports.campaign_id', '=', 'campaigns.id')->whereBetween('redtrack_reports.date', [request('start'), request('end')]);
            });
            $campaigns_query->leftJoin('providers', 'providers.id', '=', 'campaigns.provider_id');
            if (request('provider')) {
                $campaigns_query->where('campaigns.provider_id', request('provider'));
            }
            if (request('account')) {
                $campaigns_query->where('campaigns.open_id', request('account'));
            }
            if (request('advertiser')) {
                $campaigns_query->where('campaigns.advertiser_id', request('advertiser'));
            }
            if (request('search')) {
                $campaigns_query->where('name', 'LIKE', '%' . request('search') . '%');
            }
            $campaigns_query->groupBy('campaigns.id');
        } else {
            $provider = Provider::find(request('provider'));
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($provider->slug);
            $campaigns_query = (new $adVendorClass())->getCampaignQuery(request()->all());
        }
        foreach (request()->all() as $session_key => $session_value) {
            session([$session_key => $session_value]);
        }

        return new DataTableCollectionResource($campaigns_query->orderBy(request('column'), request('dir'))->paginate(request('length')));
    }

    public function filters()
    {
        $accounts = [];
        $advertisers = [];
        if (request('provider')) {
            $accounts = auth()->user()->providers()->where('provider_id', request('provider'))->get();

            if (request('account')) {
                $provider = Provider::where('id', request('provider'))->first();
                $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($provider->slug);
                $remote_advertisers = (new $adVendorClass())->advertisers();
                if ($provider->id === 2) {
                    $remote_advertisers = $remote_advertisers['marketers'];
                }
                $filtered_advertisers = UserProvider::whereHas('provider', function ($q) {
                    return $q->where('id', request('provider'))->where('open_id', request('account'));
                })->first()->advertisers;

                $advertisers = array_filter($remote_advertisers, function ($advertiser) use ($filtered_advertisers) {
                    if (request('provider') == 4 && isset($advertiser['account_id'])) {
                        $advertiser['id'] = $advertiser['account_id'];
                    }
                    return in_array($advertiser['id'], $filtered_advertisers);
                });
            }
        }

        return [
            'accounts' => $accounts,
            'advertisers' => array_map(function ($value) {
                if (isset($value['advertiserName'])) {
                    $value['name'] = $value['advertiserName'];
                }
                if (request('provider') == 4 && isset($value['account_id'])) {
                    $value['id'] = $value['account_id'];
                }

                return $value;
            }, $advertisers)
        ];
    }

    public function search()
    {
        if (request('tracker')) {
            $summary_data_query = RedtrackReport::with('campaign')->select(
                DB::raw('ROUND(SUM(cost), 2) as total_cost'),
                DB::raw('ROUND(SUM(total_revenue), 2) as total_revenue'),
                DB::raw('ROUND(SUM(profit), 2) as total_net'),
                DB::raw('ROUND((SUM(profit)/SUM(cost)) * 100, 2) as avg_roi'),
            )->whereBetween('date', [request('start'), request('end')]);
            if (request('provider')) {
                $summary_data_query->where('provider_id', request('provider'));
            }
            if (request('account')) {
                $summary_data_query->where('open_id', request('account'));
            }
            if (request('advertiser')) {
                $summary_data_query->where('advertiser_id', request('advertiser'));
            }
        } else {
            $provider = Provider::where('id', request('provider'))->first();
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($provider->slug);

            $summary_data_query = (new $adVendorClass())->getSummaryDataQuery(request()->all());
        }

        return [
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

        $campaign['provider_slug'] = $campaign->provider->slug;

        return view('campaigns.adForm', compact('campaign', 'ad_group_id'));
    }

    public function storeAd(Campaign $campaign, $ad_group_id = null)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass())->storeAd($campaign, $ad_group_id);
    }

    public function updateAd(Campaign $campaign, $ad_group_id = null)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass())->updateAd($campaign, $ad_group_id);
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
        Helper::pullCampaign();

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

    public function getCloneAd($campaign_id, $ad_group_id, $ad_id)
    {
        $campaign = Campaign::find($campaign_id);
        if (!$campaign) {
            $campaign = Campaign::where('campaign_id', $campaign_id)->first();
        }
        $instance = null;
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
        $adVendor = new $adVendorClass();
        $instance = $adVendor->getAdInstance($campaign, $ad_group_id, $ad_id);
        if (isset($instance['id'])) {
            $adVendor->cloneAdName($instance);
        } else {
            $instance = null;
        }
        $campaign['provider_slug'] = $campaign->provider->slug;

        return view('campaigns.adForm', compact('campaign', 'ad_group_id', 'instance'));
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

    public function itemStatus()
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst(request('provider'));

        return (new $adVendorClass())->itemStatus();
    }

    public function adGroupData(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass())->adGroupData($campaign);
    }

    public function adGroupSelection(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass())->adGroupSelection($campaign);
    }

    public function delete(Campaign $campaign)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
        Helper::pullCampaign();

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

    public function blockSite(Campaign $campaign, $domain_id)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass())->blockSite($campaign, $domain_id);
    }

    public function unBlockSite(Campaign $campaign, $domain_id)
    {
        $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

        return (new $adVendorClass())->unBlockSite($campaign, $domain_id);
    }
}
