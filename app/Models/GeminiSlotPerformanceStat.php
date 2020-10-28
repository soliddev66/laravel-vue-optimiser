<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiSlotPerformanceStat extends Model
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
        'pricing_type',
        'source',
        'card_id',
        'card_position',
        'ad_format_name',
        'rendered_type'
    ];
}
