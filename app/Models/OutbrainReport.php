<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutbrainReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'data',
        'date'
    ];
}
