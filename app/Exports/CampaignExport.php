<?php
namespace App\Exports;

use Carbon\Carbon;
use App\Models\Campaign;
use Maatwebsite\Excel\Concerns\FromCollection;

class CampaignExport implements FromCollection
{
    public function collection()
    {
        $result = [];

        $result[] = [
            'Actions',
            'Camp. ID',
            'Name',
            'Status',
            'Budget',
            'Avg. CPC',
            'Payout',
            'Cost',
            'Live Spent',
            'TR Conv.',
            'TR Rev.',
            'TR NET',
            'TR ROI',
            'TR EPC',
            'EPC',
            'TR CPA',
            'Imp.',
            'TS Clicks',
            'TRK Clicks',
            'LP Clicks',
            'LP CTR',
            'CTR',
            'Click Loss',
            'TS Conv.',
            'TS Rev.',
            'TS NET',
            'TS ROI',
            'TS CPA',
            'TS EPC',
            'TS CVR',
            'TR CVR',
            'eCPM',
            'LP CR',
            'LP CPC'
        ];

        $end = Carbon::now()->format('Y-m-d');
        $campaigns = Campaign::with(['redtrackReport' => function ($q) use ($end) {
            $q->whereBetween('date', [request('start'), !request('end') ? $end : request('end')]);
        }])->get();

        foreach($campaigns as $campaign) {
            $sum_conversions = $this->sum($campaign->redtrackReport, 'conversions');
            $sum_cost = $this->sum($campaign->redtrackReport, 'cost');
            $avg_cpc = $this->avg($campaign->redtrackReport, 'cpc');
            $sum_revenue = $this->sum($campaign->redtrackReport, 'revenue');
            $avg_ctr = $this->avg($campaign->redtrackReport, 'ctr');
            $sum_clicks = $this->sum($campaign->redtrackReport, 'clicks');
            $sum_prelp_clicks = $this->sum($campaign->redtrackReport, 'prelp_clicks');
            $sum_lp_clicks = $this->sum($campaign->redtrackReport, 'lp_clicks');
            $avg_roi = $this->avg($campaign->redtrackReport, 'roi');
            $sum_lp_views = $this->sum($campaign->redtrackReport, 'lp_views');

            $result[] = [
                '_',
                $campaign->campaign_id,
                $campaign->name,
                $campaign->status,
                $campaign->budget,
                strval(round($avg_cpc, 2)),
                strval($sum_conversions != 0 ? round($sum_revenue / $sum_conversions, 2) : 0),
                strval(round($sum_cost, 2)),
                strval(round($sum_cost, 2)),
                strval(round($avg_ctr, 2)),
                '_',
                '_',
                '_',
                '_',
                '_',
                '_',
                '_',
                strval(round($sum_clicks, 2)),
                strval(round($sum_prelp_clicks, 2)),
                strval(round($sum_lp_clicks, 2)),
                '_',
                '_',
                '_',
                '_',
                '_',
                strval(round($sum_revenue - $sum_cost, 2)),
                strval(round($avg_roi, 2)),
                '_',
                '_',
                '_',
                '_',
                strval($sum_lp_views != 0 ? round($sum_revenue / $sum_lp_views * 1000, 2) : 0),
                strval($sum_lp_clicks != 0 ? round($sum_conversions / $sum_lp_clicks, 2) : 0),
                strval($sum_lp_clicks != 0 ? round($sum_cost / $sum_lp_clicks, 2) : 0)
            ];
        }

        return collect($result);
    }

    private function avg($redtrackReports, $attribute)
    {
        $length = count($redtrackReports);

        if ($length == 0) {
            return 0;
        }

        $total = 0;

        foreach ($redtrackReports as $redtrackReport) {
            $total += !empty($redtrackReport[$attribute]) ? $redtrackReport[$attribute] : 0;
        }

        return round($total / $length, 2);
    }

    private function sum($redtrackReports, $attribute)
    {
        $length = count($redtrackReports);

        if ($length == 0) {
            return 0;
        }

        $total = 0;

        foreach ($redtrackReports as $redtrackReport) {
            $total += !empty($redtrackReport[$attribute]) ? $redtrackReport[$attribute] : 0;
        }

        return round($total, 2);
    }
}