<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_id',
        'campaign_id',
    ];
}
