<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'disk',
        'path'
    ];

    public function tags()
    {
        return $this->belongsToMany('App\Models\Tag', 'image_tags');
    }
}
