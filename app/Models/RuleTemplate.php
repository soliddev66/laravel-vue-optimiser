<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleTemplate extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PAUSED = 'PAUSED';

    protected $fillable = [
        'name',
        'from',
        'exclude_day',
        'rule_action_id',
        'run_type',
        'interval_amount',
        'interval_unit',
        'status'
    ];

    public function ruleConditionGroups()
    {
        return $this->hasMany(RuleConditionGroupTemplate::class);
    }
}
