<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiSearchStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'ad_group_id',
        'ad_id',
        'keyword_id',
        'delivered_match_type',
        'search_term',
        'device_type',
        'destination_url',
        'day'
    ];
}
