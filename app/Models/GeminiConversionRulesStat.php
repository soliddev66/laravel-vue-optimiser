<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiConversionRulesStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'ad_group_id',
        'rule_id',
        'rule_name',
        'category_name',
        'conversion_device',
        'keyword_id',
        'keyword_value',
        'source_name',
        'price_type',
        'day'
    ];
}
