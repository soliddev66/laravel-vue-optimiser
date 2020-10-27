<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'ad_group_id',
        'ad_id',
        'month',
        'week',
        'day',
        'hour',
        'advertiser_timezone',
        'pricing_type',
        'device_type',
        'source_name',
        'post_click_conversions',
        'post_impression_conversions',
        'ctr',
        'average_cpc',
        'average_cpm',
        'fact_conversion_counting',
        'impressions',
        'clicks',
        'conversions',
        'total_conversions',
        'average_position',
        'max_bid',
        'ad_extn_impressions',
        'spend',
        'native_bid'
    ];
}
