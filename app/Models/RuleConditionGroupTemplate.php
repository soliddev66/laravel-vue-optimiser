<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleConditionGroupTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_template_id'
    ];

    public function ruleConditions()
    {
        return $this->hasMany(RuleConditionTemplate::class);
    }
}
