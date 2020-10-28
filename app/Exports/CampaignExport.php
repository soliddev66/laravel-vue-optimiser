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
            $result[] = [
                '_',
                $campaign->campaign_id,
                $campaign->name,
                $campaign->status,
                $campaign->budget,
                isset($campaign->redtrackReport['cpc']) ? array_sum($campaign->redtrackReport['cpc']) / count($campaign->redtrackReport['cpc']) : '0',
                isset($campaign->redtrackReport['revenue']) && isset($campaign->redtrackReport['conversions']) ? round(count($campaign->redtrackReport['revenue']) / count($campaign->redtrackReport['conversions']), 2) : '0',
                isset($campaign->redtrackReport['cost']) ? count($campaign->redtrackReport['cost']) : '0',
                isset($campaign->redtrackReport['cost']) ? count($campaign->redtrackReport['cost']) : '0',
                isset($campaign->redtrackReport['ctr']) ? array_sum($campaign->redtrackReport['ctr']) / count($campaign->redtrackReport['ctr']) : '0',
                '_',
                '_',
                '_',
                '_',
                '_',
                '_',
                '_',
                isset($campaign->redtrackReport['clicks']) ? count($campaign->redtrackReport['clicks']) : '0',
                isset($campaign->redtrackReport['prelp_clicks']) ? count($campaign->redtrackReport['prelp_clicks']) : '0',
                isset($campaign->redtrackReport['lp_clicks']) ? count($campaign->redtrackReport['lp_clicks']) : '0',
                '_',
                '_',
                '_',
                '_',
                '_',
                isset($campaign->redtrackReport['revenue']) && isset($campaign->redtrackReport['cost']) ? (count($campaign->redtrackReport['revenue']) - count($campaign->redtrackReport['cost'])) : '0',
                isset($campaign->redtrackReport['roi']) ? array_sum($campaign->redtrackReport['roi']) / count($campaign->redtrackReport['roi']) : '0',
                '_',
                '_',
                '_',
                '_',
                isset($campaign->redtrackReport['revenue']) && isset($campaign->redtrackReport['lp_views']) ? round(count($campaign->redtrackReport['revenue']) / (count($campaign->redtrackReport['lp_views']) * 1000), 2) : '0',
                isset($campaign->redtrackReport['conversions']) && isset($campaign->redtrackReport['lp_clicks']) ? round(count($campaign->redtrackReport['conversions']) / count($campaign->redtrackReport['lp_clicks']), 2) : '0',
                isset($campaign->redtrackReport['cost']) && isset($campaign->redtrackReport['lp_clicks']) ? round(count($campaign->redtrackReport['cost']) / count($campaign->redtrackReport['lp_clicks']), 2) : '0'
            ];
        }

        return collect($result);
    }
}