<?php
namespace App\Exports;

use Carbon\Carbon;
use App\Models\Campaign;
use App\Utils\ReportData;
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
            $sum_conversions = ReportData::sum($campaign, $campaign->redtrackReport, 'conversions');
            $sum_cost = ReportData::sum($campaign, $campaign->redtrackReport, 'cost');
            $avg_cpc = ReportData::avg($campaign->redtrackReport, 'cpc');
            $sum_revenue = ReportData::sum($campaign, $campaign->redtrackReport, 'revenue');
            $avg_ctr = ReportData::avg($campaign->redtrackReport, 'ctr');
            $sum_clicks = ReportData::sum($campaign, $campaign->redtrackReport, 'clicks');
            $sum_prelp_clicks = ReportData::sum($campaign, $campaign->redtrackReport, 'prelpClicks');
            $sum_lp_clicks = ReportData::sum($campaign, $campaign->redtrackReport, 'lpClicks');
            $avg_roi = ReportData::avg($campaign->redtrackReport, 'roi');
            $sum_lp_views = ReportData::sum($campaign, $campaign->redtrackReport, 'lpViews');

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


}