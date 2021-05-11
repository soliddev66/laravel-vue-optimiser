<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreativeSet extends Model
{
    use HasFactory;

    public function imageSets()
    {
        return $this->belongsToMany(ImageSet::class, 'creative_set_sets', 'creative_set_id', 'set_id');
    }

    public function videoSets()
    {
        return $this->belongsToMany(VideoSet::class, 'creative_set_sets', 'creative_set_id', 'set_id');
    }

    public function titleSets()
    {
        return $this->belongsToMany(TitleSet::class, 'creative_set_sets', 'creative_set_id', 'set_id');
    }

    public function descriptionSets()
    {
        return $this->belongsToMany(DescriptionSet::class, 'creative_set_sets', 'creative_set_id', 'set_id');
    }

    public function creativeSetSets()
    {
        return $this->hasMany(CreativeSetSet::class);
    }
}
