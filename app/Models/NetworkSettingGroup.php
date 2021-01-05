<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkSettingGroup extends Model
{
    use HasFactory;

    public function groups()
    {
        return $this->hasMany(NetworkSettingGroup::class, 'parent');
    }

    public function networkSettings()
    {
        return $this->hasMany(NetworkSetting::class);
    }
}
