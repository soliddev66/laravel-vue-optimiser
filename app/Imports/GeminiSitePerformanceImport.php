<?php

namespace App\Imports;

use App\Models\GeminiSitePerformanceStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class GeminiSitePerformanceImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow, WithEvents
{
    use Importable;

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_site_performance_stat = GeminiSitePerformanceStat::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'ad_group_id' => $row['ad_group_id'] ?? '',
            'day' => $row['day'],
            'external_site_name' => $row['external_site_name'],
            'external_site_group_name' => $row['external_site_group_name'],
            'device_type' => $row['device_type'],
            'bid_modifier' => $row['bid_modifier'],
            'average_bid' => $row['average_bid'],
            'modified_bid' => $row['modified_bid']
        ]);

        foreach (array_keys($row) as $array_key) {
            $gemini_site_performance_stat->{$array_key} = $row[$array_key];
        }

        $gemini_site_performance_stat->save();
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
