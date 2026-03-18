<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Elements extends Model
{
    protected $table = 'elements';
    protected $primaryKey = 'id_element';
    protected $fillable = ['libelle_element'];

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
