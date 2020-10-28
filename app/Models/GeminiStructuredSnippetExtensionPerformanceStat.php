<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiStructuredSnippetExtensionPerformanceStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'ad_group_id',
        'ad_id',
        'keyword_id',
        'structured_snippet_extn_id',
        'month',
        'week',
        'day',
        'pricing_type',
        'source',
        'destination_url'
    ];
}
