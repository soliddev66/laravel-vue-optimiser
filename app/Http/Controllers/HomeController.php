<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\RedtrackReport;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (request()->ajax()) {
            $start_date = Carbon::now()->subDays(30)->format('Y-m-d');
            $end_date = Carbon::now()->format('Y-m-d');
            if (request('start')) {
                $start_date = request('start');
            }
            if (request('end')) {
                $end_date = request('end');
            }

            $summary_data = $this->getQuery($start_date, $end_date)->first();
            $data_by_date = $this->getQuery($start_date, $end_date, 'date')->groupBy('date')->get();
            $top_winners = $this->getQuery($start_date, $end_date, 'provider_id')->having(DB::raw('SUM(profit)'), '>=', 0)->groupBy('provider_id')->get();
            $top_losers = $this->getQuery($start_date, $end_date, 'provider_id')->having(DB::raw('SUM(profit)'), '<', 0)->groupBy('provider_id')->get();

            return response()->json([
                'summary_data' => $summary_data,
                'data_by_date' => $data_by_date,
                'top_winners' => $top_winners,
                'top_losers' => $top_losers
            ]);
        }

        return view('home');
    }

    public function getDataByProvider()
    {
        $start_date = Carbon::now()->subDays(30)->format('Y-m-d');
        $end_date = Carbon::now()->format('Y-m-d');
        if (request('start')) {
            $start_date = request('start');
        }
        if (request('end')) {
            $end_date = request('end');
        }
        $data_by_provider = $this->getDataByProviderQuery($start_date, $end_date)->groupBy('providers.id')->orderBy(request('column'), request('dir'))->paginate(request('length'));
        return new DataTableCollectionResource($data_by_provider);
    }

    private function getDataByProviderQuery($start_date, $end_date)
    {
        return Provider::select(
                DB::raw('providers.id as provider_id'),
                DB::raw('MAX(label) as name'),
                DB::raw('ROUND(SUM(cost), 2) as total_cost'),
                DB::raw('ROUND(SUM(profit), 2) as total_net'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('ROUND(SUM(cost)/SUM(total_conversions), 2) as cpa'),
                DB::raw('ROUND(SUM(total_revenue), 2) as total_revenue'),
                DB::raw('ROUND((SUM(profit)/SUM(cost)) * 100, 2) as roi'),
                DB::raw('ROUND(SUM(total_conversions), 2) as total_conversions'),
                DB::raw('ROUND(SUM(total_revenue)/SUM(clicks), 2) as epc')
            )
            ->leftJoin('redtrack_reports', function ($join) use ($start_date, $end_date) {
                $join->on('providers.id', '=', 'redtrack_reports.provider_id');
                $join->whereBetween('redtrack_reports.date', [$start_date, $end_date]);
            })
            ->where('providers.label', 'LIKE', '%' . request('search') . '%');
    }

    private function getQuery($start_date, $end_date, $field = '')
    {
        if ($field) {
            return RedtrackReport::select(
                $field,
                DB::raw('MAX(providers.label) as name'),
                DB::raw('MAX(providers.icon) as icon'),
                DB::raw('ROUND(SUM(cost), 2) as total_cost'),
                DB::raw('ROUND(SUM(profit), 2) as total_net'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('ROUND(SUM(cost)/SUM(total_conversions), 2) as cpa'),
                DB::raw('ROUND(SUM(total_revenue), 2) as total_revenue'),
                DB::raw('ROUND((SUM(profit)/SUM(cost)) * 100, 2) as roi'),
                DB::raw('ROUND(SUM(total_conversions), 2) as total_conversions'),
                DB::raw('ROUND(SUM(total_revenue)/SUM(clicks), 2) as epc')
            )
            ->join('providers', 'providers.id', '=', 'redtrack_reports.provider_id')
            ->where('providers.label', 'LIKE', '%' . request('search') . '%')
            ->whereBetween('date', [$start_date, $end_date]);
        } else {
            return RedtrackReport::select(
                DB::raw('ROUND(SUM(cost), 2) as total_cost'),
                DB::raw('ROUND(SUM(profit), 2) as total_net'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('ROUND(SUM(cost)/SUM(total_conversions), 2) as cpa'),
                DB::raw('ROUND(SUM(total_revenue), 2) as total_revenue'),
                DB::raw('ROUND((SUM(profit)/SUM(cost)) * 100, 2) as roi'),
                DB::raw('ROUND(SUM(total_conversions), 2) as total_conversions'),
                DB::raw('ROUND(SUM(total_revenue)/SUM(clicks), 2) as epc')
            )->whereBetween('date', [$start_date, $end_date]);
        }
    }
}
