<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiCampaignBidPerformanceStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'section_id',
        'ad_group_id',
        'day',
        'supply_type',
        'group_or_site',
        'group',
        'bid_modifier',
        'average_bid',
        'modified_bid'
    ];
}
