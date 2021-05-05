<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_id',
        'type',
        'user_id',
        'provider_id',
        'campaign_id',
        'advertiser_id',
        'ad_group_id',
        'open_id',
        'video',
        'image',
        'description',
        'synced',
        'status',
        'image'
    ];

    public function redtrackContentStats()
    {
        return $this->hasMany(RedtrackContentStat::class, 'sub5', 'ad_id');
    }

    public function creativeSets()
    {
        return $this->hasMany(CreativeSet::class);
    }
}
