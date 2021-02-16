<?php

namespace App\Imports;

use App\Models\GeminiConversionRulesStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class GeminiConversionRulesImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow, WithEvents, WithBatchInserts
{
    use Importable;

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        // $gemini_conversion_rules_stat = GeminiConversionRulesStat::firstOrNew([
        //     'advertiser_id' => $row['advertiser_id'],
        //     'campaign_id' => $row['campaign_id'],
        //     'ad_group_id' => $row['ad_group_id'],
        //     'rule_id' => $row['rule_id'],
        //     'rule_name' => $row['rule_name'],
        //     'category_name' => $row['category_name'],
        //     'conversion_device' => $row['conversion_device'],
        //     'keyword_id' => $row['keyword_id'],
        //     'keyword_value' => $row['keyword_value'],
        //     'source_name' => $row['source_name'],
        //     'price_type' => $row['price_type'],
        //     'day' => $row['day']
        // ]);

        $gemini_conversion_rules_stat = new GeminiConversionRulesStat();

        foreach (array_keys($row) as $array_key) {
            $gemini_conversion_rules_stat->{$array_key} = $row[$array_key];
        }

        $gemini_conversion_rules_stat->save();
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
