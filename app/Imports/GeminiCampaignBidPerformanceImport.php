<?php

namespace App\Imports;

use App\Models\GeminiCampaignBidPerformanceStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class GeminiCampaignBidPerformanceImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow, WithEvents, WithBatchInserts
{
    use Importable;

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_campaign_bid_performance_stat = GeminiCampaignBidPerformanceStat::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'section_id' => $row['section_id'],
            'ad_group_id' => $row['ad_group_id'],
            'day' => $row['day'],
            'supply_type' => $row['supply_type'],
            'group_or_site' => $row['group_or_site'],
            'group' => $row['group'],
            'bid_modifier' => $row['bid_modifier'],
            'average_bid' => $row['average_bid'],
            'modified_bid' => $row['modified_bid']
        ]);

        foreach (array_keys($row) as $array_key) {
            $gemini_campaign_bid_performance_stat->{$array_key} = $row[$array_key];
        }

        $gemini_campaign_bid_performance_stat->save();
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 500;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 500;
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
