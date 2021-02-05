<?php

namespace App\Imports;

use App\Models\GeminiPerformanceStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class GeminiPerformanceImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow, WithEvents
{
    use Importable;

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_performance_stat = GeminiPerformanceStat::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'ad_group_id' => $row['ad_group_id'],
            'ad_id' => $row['ad_id'],
            'month' => $row['month'],
            'week' => $row['week'],
            'day' => $row['day'],
            'hour' => $row['hour'],
            'pricing_type' => $row['pricing_type'],
            'device_type' => $row['device_type'],
            'source_name' => $row['source_name']
        ]);

        foreach (array_keys($row) as $array_key) {
            $gemini_performance_stat->{$array_key} = $row[$array_key];
        }

        $gemini_performance_stat->save();
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
