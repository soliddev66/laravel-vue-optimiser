<?php

namespace App\Models;

use Exception;

use App\Models\RedtrackReport;
use App\Models\GeminiPerformanceStat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PAUSED = 'PAUSED';

    protected $fillable = [
        'user_id',
        'provider_id',
        'open_id',
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
        'is_deep_link',
        'tag_id'
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

    public function performanceStats()
    {
        return $this->hasMany(GeminiPerformanceStat::class, 'campaign_id', 'campaign_id');
    }

    public function provider()
    {
        return $this->belongsTo('App\Models\Provider');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function status($gemini, $status)
    {
        $ad_group_body = [];
        $ad_group_ids = [];
        $ad_body = [];

        try {
            $this->status = $status;
            $gemini->updateCampaignStatus($this);
            $ad_groups = $gemini->getAdGroups($this->campaign_id, $this->advertiser_id);

            foreach ($ad_groups as $ad_group) {
                $ad_group_body[] = [
                    'id' => $ad_group['id'],
                    'status' => $this->status
                ];
                $ad_group_ids[] = $ad_group['id'];
            }

            $gemini->updateAdGroups($ad_group_body);
            $ads = $gemini->getAds($ad_group_ids, $this->advertiser_id);

            foreach ($ads as $ad) {
                $ad_body[] = [
                    'adGroupId' => $ad['adGroupId'],
                    'id' => $ad['id'],
                    'status' => $this->status
                ];
            }

            $gemini->updateAds($ad_body);
            $this->save();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
