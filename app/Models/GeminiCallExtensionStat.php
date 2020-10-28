<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiCallExtensionStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'ad_group_id',
        'month',
        'week',
        'day'
    ];
}
