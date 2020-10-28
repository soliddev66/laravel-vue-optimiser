<?php

namespace App\Imports;

use App\Models\GeminiAdjustmentStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class GeminiAdjustmentImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow
{
    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_adjustment_stat = GeminiAdjustmentStat::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'day' => $row['day'],
            'pricing_type' => $row['pricing_type'],
            'source_name' => $row['source_name'],
            'is_adjustment' => $row['is_adjustment'],
            'is_adjustment' => $row['is_adjustment'],
            'adjustment_type' => $row['adjustment_type']
        ]);

        foreach (array_keys($row) as $array_key) {
            $gemini_adjustment_stat->{$array_key} = $row[$array_key];
        }

        $gemini_adjustment_stat->save();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
