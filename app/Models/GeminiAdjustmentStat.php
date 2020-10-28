<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiAdjustmentStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'day',
        'pricing_type',
        'source_name',
        'is_adjustment',
        'is_adjustment',
        'adjustment_type'
    ];
}
