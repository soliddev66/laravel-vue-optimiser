<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaboolaReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'start_date',
        'end_date',
        'date',
        'date_end_period',
        'clicks',
        'impressions',
        'visible_impressions',
        'spent',
        'conversions_value',
        'roas',
        'ctr',
        'vctr',
        'cpm',
        'vcpm',
        'cpc',
        'campaigns_num',
        'cpa',
        'cpa_clicks',
        'cpa_views',
        'cpa_actions_num',
        'cpa_actions_num_from_clicks',
        'cpa_actions_num_from_views',
        'cpa_conversion_rate',
        'cpa_conversion_rate_clicks',
        'cpa_conversion_rate_views',
        'currency'
    ];
}
