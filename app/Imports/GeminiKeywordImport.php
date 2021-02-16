<?php

namespace App\Imports;

use App\Models\GeminiKeywordStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class GeminiKeywordImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow, WithEvents, WithBatchInserts
{
    use Importable;

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        // $gemini_keyword_stat = GeminiKeywordStat::firstOrNew([
        //     'advertiser_id' => $row['advertiser_id'],
        //     'campaign_id' => $row['campaign_id'],
        //     'ad_group_id' => $row['ad_group_id'],
        //     'ad_id' => $row['ad_id'],
        //     'keyword_id' => $row['keyword_id'],
        //     'destination_url' => $row['destination_url'],
        //     'day' => $row['day'],
        //     'device_type' => $row['device_type'],
        //     'source_name' => $row['source_name']
        // ]);

        $gemini_keyword_stat = new GeminiKeywordStat();

        foreach (array_keys($row) as $array_key) {
            $gemini_keyword_stat->{$array_key} = $row[$array_key];
        }

        $gemini_keyword_stat->save();
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
