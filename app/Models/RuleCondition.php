<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_condition_group_id',
        'rule_condition_type_id',
        'operation',
        'amount',
        'unit'
    ];
}
