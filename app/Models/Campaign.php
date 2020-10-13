<?php

namespace App\Models;

use App\Models\RedtrackReport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'name',
        'status',
        'advertiser_id',
        'language',
        'tracking_url',
        'objective',
        'advanced_geo_pos',
        'advanced_geo_neg',
        'conversion_rule_ids',
        'bidding_strategy',
        'effective_status',
        'budget_type',
        'conversion_rule_config',
        'budget',
        'channel',
        'created_date',
        'last_update_date',
        'is_partner_network',
        'sub_channel_modifier',
        'custom_parameters',
        'sub_channel',
        'editorial_status',
        'is_deep_link'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'conversion_rule_config' => 'array',
    ];

    public function redtrackReport()
    {
        return $this->hasMany(RedtrackReport::class);
    }
}
