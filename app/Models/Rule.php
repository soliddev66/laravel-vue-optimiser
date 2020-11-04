<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PAUSED = 'PAUSED';

    protected $fillable = [
        'user_id',
        'name',
        'rule_group_id',
        'from',
        'exclude_day',
        'run_type',
        'interval_amount',
        'interval_unit',
        'user_id',
        'status'
    ];

    public function ruleConditionGroups()
    {
        return $this->hasMany(RuleConditionGroup::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'rule_campaigns');
    }
}
