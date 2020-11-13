<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleCondition extends Model
{
    use HasFactory;

    const OPERATIONS = [
        1 => 'LESS_THAN',
        2 => 'GREATER_THAN',
        3 => 'EQUAL'
    ];

    protected $fillable = [
        'rule_condition_group_id',
        'rule_condition_type_id',
        'operation',
        'amount',
        'unit'
    ];

    public function ruleConditionType() {
        return $this->belongsTo(RuleConditionType::class, 'rule_condition_type_id');
    }
}
