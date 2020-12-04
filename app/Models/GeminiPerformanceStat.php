<?php

namespace App\Models;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiPerformanceStat extends Model
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
        'device_type',
        'source_name'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
