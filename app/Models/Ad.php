<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_id',
        'user_id',
        'provider_id',
        'campaign_id',
        'advertiser_id',
        'ad_group_id',
        'open_id',
        'image',
        'description',
        'synced'
    ];

    public function redtrackContentStats()
    {
        return $this->hasMany(RedtrackContentStat::class, 'sub5', 'ad_id');
    }
}
