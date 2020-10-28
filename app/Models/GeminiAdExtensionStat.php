<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiAdExtensionStat extends Model
{
    use HasFactory;

    protected $table = 'gemini_ad_extension_details';

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'ad_group_id',
        'ad_id',
        'keyword_id',
        'ad_extn_id',
        'device_type',
        'month',
        'week',
        'day',
        'pricing_type',
        'destination_url'
    ];
}
