<?php

namespace App\Imports;

use App\Models\GeminiStructuredSnippetExtensionPerformanceStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class GeminiStructuredSnippetExtensionPerformanceImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow
{
    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_structured_snippet_extension_performance_stat = GeminiStructuredSnippetExtensionPerformanceStat::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'ad_group_id' => $row['ad_group_id'],
            'ad_id' => $row['ad_id'],
            'keyword_id' => $row['keyword_id'],
            'structured_snippet_extn_id' => $row['structured_snippet_extn_id'],
            'month' => $row['month'],
            'week' => $row['week'],
            'day' => $row['day'],
            'pricing_type' => $row['pricing_type'],
            'source' => $row['source'],
            'destination_url' => $row['destination_url']
        ]);

        foreach (array_keys($row) as $array_key) {
            $gemini_structured_snippet_extension_performance_stat->{$array_key} = $row[$array_key];
        }

        $gemini_structured_snippet_extension_performance_stat->save();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
