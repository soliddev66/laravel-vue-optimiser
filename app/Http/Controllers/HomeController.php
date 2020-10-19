<?php

namespace App\Http\Controllers;

use App\Models\RedtrackReport;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $start_date = Carbon::now()->format('Y-m-d');
        $end_date = Carbon::now()->format('Y-m-d');
        if (request('start')) {
            $start_date = request('start');
        }
        if (request('end')) {
            $end_date = request('end');
        }

        $data = RedtrackReport::select(
            DB::raw('SUM(cost) as total_cost'),
            DB::raw('SUM(profit) as total_net'),
            DB::raw('SUM(clicks) as total_clicks'),
            DB::raw('SUM(cpa)/COUNT(*) as avg_cpa'),
            DB::raw('SUM(total_revenue) as total_revenue'),
            DB::raw('SUM(roi)/COUNT(*) as avg_roi'),
            DB::raw('SUM(total_conversions) as total_conversions'),
            DB::raw('SUM(epc)/COUNT(*) as avg_epc'),
        )->whereBetween('date', [$start_date, $end_date])->get();

        if (request()->ajax()) {
            return response()->json([
                'data' => $data
            ]);
        }

        return view('home', compact('data'));
    }
}
