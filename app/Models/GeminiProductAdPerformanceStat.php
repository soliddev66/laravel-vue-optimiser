<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiProductAdPerformanceStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'ad_group_id',
        'product_ad_id',
        'month',
        'week',
        'day',
        'pricing_type',
        'device_type',
        'source_name'
    ];
}
