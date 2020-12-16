<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedtrackDomainStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'sub_1',
        'sub_2',
        'sub_3',
        'sub_4',
        'sub_5',
        'sub_6',
        'sub_7',
    ];
}
