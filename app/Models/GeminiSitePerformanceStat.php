<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiSitePerformanceStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'ad_group_id',
        'day',
        'external_site_name',
        'external_site_group_name',
        'device_type',
        'bid_modifier',
        'average_bid',
        'modified_bid'
    ];
}
