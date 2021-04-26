<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreativeSet extends Model
{
    use HasFactory;

    public function imageSets()
    {
        return $this->belongsToMany('App\Models\ImageSet', 'creative_set_sets', 'creative_set_id', 'set_id');
    }

    public function videoSets()
    {
        return $this->belongsToMany('App\Models\VideoSet', 'creative_set_sets', 'creative_set_id', 'set_id');
    }

    public function titleSets()
    {
        return $this->belongsToMany('App\Models\TitleSet', 'creative_set_sets', 'creative_set_id', 'set_id');
    }

    public function creativeSetSets()
    {
        return $this->hasMany('App\Models\CreativeSetSet');
    }

    public function deleteRelations()
    {
        $this->imageSets()->delete();
        $this->videoSets()->delete();
        $this->titleSets()->delete();
        $this->creativeSetSets()->delete();
    }
}
