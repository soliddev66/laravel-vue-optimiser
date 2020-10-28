<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiUserStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'audience_id',
        'audience_name',
        'audience_type',
        'audience_status',
        'ad_group_id',
        'day',
        'pricing_type',
        'source_name',
        'gender',
        'age',
        'device_type',
        'country',
        'state',
        'city',
        'zip',
        'dma_woeid',
        'city_woeid',
        'state_woeid',
        'zip_woeid',
        'country_woeid',
        'location_type'
    ];
}
