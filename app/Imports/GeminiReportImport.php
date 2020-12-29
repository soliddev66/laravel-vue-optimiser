<?php

namespace App\Imports;

use App\Models\GeminiReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class GeminiReportImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow, WithEvents
{
    use Importable;

    /**
     * @param Row $row
     */
    public function onRow(Row $row) {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_report = GeminiReport::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'ad_group_id' => $row['ad_group_id'],
            'ad_id' => $row['ad_id'],
            'month' => $row['month'],
            'week' => $row['week'],
            'day' => $row['day'],
            'hour' => $row['hour'],
            'advertiser_timezone' => $row['advertiser_timezone'],
            'pricing_type' => $row['pricing_type'],
            'device_type' => $row['device_type'],
            'source_name' => $row['source_name'],
            'fact_conversion_counting' => $row['fact_conversion_counting']
        ]);

        $gemini_report->post_click_conversions = $row['post_click_conversions'];
        $gemini_report->post_impression_conversions = $row['post_impression_conversions'];
        $gemini_report->ctr = $row['ctr'];
        $gemini_report->average_cpc = $row['average_cpc'];
        $gemini_report->average_cpm = $row['average_cpm'];
        $gemini_report->impressions = $row['impressions'];
        $gemini_report->clicks = $row['clicks'];
        $gemini_report->conversions = $row['conversions'];
        $gemini_report->total_conversions = $row['total_conversions'];
        $gemini_report->average_position = $row['average_position'];
        $gemini_report->max_bid = $row['max_bid'];
        $gemini_report->ad_extn_impressions = $row['ad_extn_impressions'];
        $gemini_report->spend = $row['spend'];
        $gemini_report->native_bid = $row['native_bid'];

        $gemini_report->save();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            // Array callable, refering to a static method.
            AfterImport::class => [self::class, 'afterSheet'],
        ];
    }

    public static function afterSheet(AfterImport $event)
    {
        //
    }
}
