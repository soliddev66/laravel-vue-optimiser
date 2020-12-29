<?php

namespace App\Imports;

use App\Models\GeminiAdExtensionStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Jobs\AfterImportJob;
use Maatwebsite\Excel\Row;

class GeminiAdExtensionImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow, WithEvents
{
    use Importable;

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_ad_extension_stat = GeminiAdExtensionStat::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'ad_group_id' => $row['ad_group_id'],
            'ad_id' => $row['ad_id'],
            'keyword_id' => $row['keyword_id'],
            'ad_extn_id' => $row['ad_extn_id'],
            'device_type' => $row['device_type'],
            'month' => $row['month'],
            'week' => $row['week'],
            'day' => $row['day'],
            'pricing_type' => $row['pricing_type'],
            'destination_url' => $row['destination_url']
        ]);

        foreach (array_keys($row) as $array_key) {
            $gemini_ad_extension_stat->{$array_key} = $row[$array_key];
        }

        $gemini_ad_extension_stat->save();
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
