<?php

namespace App\Imports;

use App\Models\GeminiSlotPerformanceStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class GeminiSlotPerformanceImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow
{
    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_slot_performance_stat = GeminiSlotPerformanceStat::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'ad_group_id' => $row['ad_group_id'],
            'ad_id' => $row['ad_id'],
            'month' => $row['month'],
            'week' => $row['week'],
            'day' => $row['day'],
            'hour' => $row['hour'],
            'pricing_type' => $row['pricing_type'],
            'source' => $row['source'],
            'card_id' => $row['card_id'],
            'card_position' => $row['card_position'],
            'ad_format_name' => $row['ad_format_name'],
            'rendered_type' => $row['rendered_type']
        ]);

        foreach (array_keys($row) as $array_key) {
            $gemini_slot_performance_stat->{$array_key} = $row[$array_key];
        }

        $gemini_slot_performance_stat->save();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
