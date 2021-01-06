<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'network_setting_group_id',
        'site_block',
        'group_1a',
        'group_1b',
        'group_2a',
        'group_2b',
        'group_3a',
        'group_3b',
    ];
}
