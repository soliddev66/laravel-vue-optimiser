<?php

namespace App\Models;

use App\Models\Campaign;
use App\Models\Provider;
use App\Models\Tracker;
use App\Models\UserProvider;
use App\Models\UserTracker;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function providers()
    {
        return $this->hasMany(UserProvider::class);
    }

    public function trackers()
    {
        return $this->hasMany(UserTracker::class);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function rules()
    {
        return $this->hasMany(Rule::class);
    }

    public function ruleGroups()
    {
        return $this->hasMany(RuleGroup::class);
    }

    public function creativeSets()
    {
        return $this->hasMany(CreativeSet::class);
    }
}
