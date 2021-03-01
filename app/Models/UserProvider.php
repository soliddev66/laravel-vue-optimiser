<?php

namespace App\Models;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_id',
        'open_id',
        'basic_auth',
        'token',
        'refresh_token',
        'expires_in'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'advertisers' => 'array',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
