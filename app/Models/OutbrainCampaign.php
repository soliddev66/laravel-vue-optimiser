<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutbrainCampaign extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PAUSED = 'PAUSED';

    /**
     * Mass assignable attributes.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'provider_id',
        'open_id',
        'campaign_id',
        'name',
        'marketer_id',
        'enabled',
        'cpc',
        'minimum_cpc',
        'currency',
        'auto_archived',
        'targeting',
        'budget',
        'feeds',
        'auto_expiration_of_promoted_links',
        'content_type',
        'suffix_tracking_code',
        'prefix_tracking_code',
        'last_modified',
        'creation_time',
        'live_status',
        'cpc_per_ad_enabled',
        'blocked_sites',
        'start_hour',
        'tracking_pixels',
        'bids',
        'campaign_optimization',
        'on_air_reason',
        'campaign_on_air',
        'scheduling',
        'objective',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'targeting' => 'json',
        'budget' => 'json',
        'feeds' => 'json',
        'blocked_sites' => 'json',
        'tracking_pixels' => 'json',
        'bids' => 'json',
        'campaign_optimization' => 'json',
        'scheduling' => 'json',
        'prefix_tracking_code' => 'json',
        'live_status' => 'json',
    ];

    /**
     * RedTrack report
     *
     * @return HasMany
     */
    public function redtrackReport()
    {
        return $this->hasMany(RedtrackReport::class);
    }

    /**
     * Provider
     *
     * @return BelongsTo
     */
    public function provider()
    {
        return $this->belongsTo('App\Models\Provider');
    }
}
