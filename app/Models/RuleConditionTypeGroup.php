<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleConditionTypeGroup extends Model
{
    use HasFactory;

    public function ruleConditionTypes()
    {
        return $this->hasMany(RuleConditionType::class);
    }
}
