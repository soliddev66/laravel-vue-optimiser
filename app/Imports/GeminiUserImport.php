<?php

namespace App\Imports;

use App\Models\GeminiUserStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class GeminiUserImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow, WithEvents
{
    use Importable;

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_user_stat = GeminiUserStat::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'audience_id' => $row['audience_id'],
            'audience_name' => $row['audience_name'],
            'audience_type' => $row['audience_type'],
            'audience_status' => $row['audience_status'],
            'ad_group_id' => $row['ad_group_id'],
            'day' => $row['day'],
            'pricing_type' => $row['pricing_type'],
            'source_name' => $row['source_name'],
            'gender' => $row['gender'],
            'age' => $row['age'],
            'device_type' => $row['device_type'],
            'country' => $row['country'],
            'state' => $row['state'],
            'city' => $row['city'],
            'zip' => $row['zip'],
            'dma_woeid' => $row['dma_woeid'],
            'city_woeid' => $row['city_woeid'],
            'state_woeid' => $row['state_woeid'],
            'zip_woeid' => $row['zip_woeid'],
            'country_woeid' => $row['country_woeid'],
            'location_type' => $row['location_type']
        ]);

        foreach (array_keys($row) as $array_key) {
            $gemini_user_stat->{$array_key} = $row[$array_key];
        }

        $gemini_user_stat->save();
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
