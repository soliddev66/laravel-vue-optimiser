<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasFactory;

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

}
