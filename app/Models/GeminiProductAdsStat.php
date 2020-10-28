<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiProductAdsStat extends Model
{
    use HasFactory;

    protected $table = 'gemini_product_ads';

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'ad_group_id',
        'offer_id',
        'category_id',
        'category_name',
        'device',
        'product_type',
        'brand',
        'offer_group_id',
        'product_id',
        'product_name',
        'custom_label_0',
        'custom_label_1',
        'custom_label_2',
        'custom_label_3',
        'custom_label_4',
        'source',
        'device_type',
        'month',
        'week',
        'day'
    ];
}
