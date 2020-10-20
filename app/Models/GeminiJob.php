<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'advertiser_id',
        'job_id',
        'status',
        'job_response',
        'submited_at'
    ];
}
