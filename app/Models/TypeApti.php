<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TypeApti extends Model
{
    public $timestamps = false;
    protected $table = 'type_apti';
    protected $primaryKey = 'id_TypeApti';

    protected $fillable = ['libelle_Apti'];

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
