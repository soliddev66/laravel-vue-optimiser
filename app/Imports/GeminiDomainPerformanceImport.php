<?php

namespace App\Imports;

use App\Models\GeminiDomainPerformanceStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class GeminiDomainPerformanceImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow
{
    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_domain_performance_stat = GeminiDomainPerformanceStat::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'ad_group_id' => $row['ad_group_id'],
            'top_domain' => $row['top_domain'],
            'package_name' => $row['package_name'],
            'day' => $row['day']
        ]);

        foreach (array_keys($row) as $array_key) {
            $gemini_domain_performance_stat->{$array_key} = $row[$array_key];
        }

        $gemini_domain_performance_stat->save();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}