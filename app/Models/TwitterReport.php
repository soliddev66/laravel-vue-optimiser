<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwitterReport extends Model
{
    use HasFactory;

    const METRIC_GROUPS = ['BILLING', 'ENGAGEMENT', 'LIFE_TIME_VALUE_MOBILE_CONVERSION', 'MEDIA', 'MOBILE_CONVERSION', 'VIDEO', 'WEB_CONVERSION'];

    const PLACEMENTS = ['ALL_ON_TWITTER', 'PUBLISHER_NETWORK'];

    protected $fillable = [
        'campaign_id',
        'granularity',
        'placement',
        'data',
        'start_time',
        'end_time'
    ];
}
