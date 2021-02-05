<?php

namespace App\Imports;

use App\Models\GeminiProductAdsStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class GeminiProductAdsImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow, WithEvents
{
    use Importable;

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_product_ads_stat = GeminiProductAdsStat::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'ad_group_id' => $row['ad_group_id'],
            'offer_id' => $row['offer_id'],
            'category_id' => $row['category_id'],
            'category_name' => $row['category_name'],
            'device' => $row['device'],
            'product_type' => $row['product_type'],
            'brand' => $row['brand'],
            'offer_group_id' => $row['offer_group_id'],
            'product_id' => $row['product_id'],
            'product_name' => $row['product_name'],
            'custom_label_0' => $row['custom_label_0'],
            'custom_label_1' => $row['custom_label_1'],
            'custom_label_2' => $row['custom_label_2'],
            'custom_label_3' => $row['custom_label_3'],
            'custom_label_4' => $row['custom_label_4'],
            'source' => $row['source'],
            'device_type' => $row['device_type'],
            'month' => $row['month'],
            'week' => $row['week'],
            'day' => $row['day']
        ]);

        foreach (array_keys($row) as $array_key) {
            $gemini_product_ads_stat->{$array_key} = $row[$array_key];
        }

        $gemini_product_ads_stat->save();
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
