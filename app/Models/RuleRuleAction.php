<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleRuleAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_id',
        'rule_action_id',
        'action_data',
    ];

    public function ruleAction()
    {
        return $this->belongsTo(RuleAction::class);
    }
}
