<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleConditionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_id'
    ];

    public function ruleConditions()
    {
        return $this->hasMany(RuleCondition::class);
    }
}
