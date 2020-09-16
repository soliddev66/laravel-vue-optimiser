<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTracker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tracker_id',
        'open_id',
        'api_key',
        'email',
        'name'
    ];
}
