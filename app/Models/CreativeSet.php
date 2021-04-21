<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreativeSet extends Model
{
    use HasFactory;

    public function mediaSets()
    {
        return $this->belongsToMany('App\Models\MediaSet', 'creative_set_sets', 'creative_set_id', 'set_id');
    }

    public function titleSets()
    {
        return $this->belongsToMany('App\Models\TitleSet', 'creative_set_sets', 'creative_set_id', 'set_id');
    }
}
