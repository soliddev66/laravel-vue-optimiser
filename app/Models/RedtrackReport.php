<?php

namespace App\Models;

use App\Models\Campaign;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedtrackReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'open_id',
        'provider_id',
        'approved',
        'attribution',
        'baddevice',
        'blacklist',
        'clicks',
        'conversions',
        'convtype1',
        'convtype2',
        'convtype3',
        'convtype4',
        'convtype5',
        'convtype6',
        'convtype7',
        'convtype8',
        'convtype9',
        'convtype10',
        'cost',
        'cpa',
        'cpc',
        'cpt',
        'cr',
        'ctr',
        'datacenter',
        'declined',
        'epc',
        'hour_of_day',
        'impressions',
        'impressions_visible',
        'lp_clicks',
        'lp_ctr',
        'lp_views',
        'ok',
        'other',
        'pending',
        'prelp_views',
        'prelp_clicks',
        'profit',
        'pubrevenue',
        'revenue',
        'revenuetype1',
        'revenuetype2',
        'revenuetype3',
        'revenuetype4',
        'revenuetype5',
        'revenuetype6',
        'revenuetype7',
        'revenuetype8',
        'revenuetype9',
        'revenuetype10',
        'roi',
        'sub1',
        'sub2',
        'sub3',
        'sub4',
        'sub5',
        'sub6',
        'sub7',
        'total_conversions',
        'total_revenue',
        'tr',
        'transactions',
        'unique_clicks',
        'date',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
