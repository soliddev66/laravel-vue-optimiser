<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_group_id',
        'user_id',
        'provider_id',
        'campaign_id',
        'advertiser_id',
        'open_id'
    ];
}
