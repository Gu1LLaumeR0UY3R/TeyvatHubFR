<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TypeEnnemi extends Model
{
    public $timestamps = false;
    protected $table = 'type_ennemi';
    protected $primaryKey = 'id_typeEnnemi';

    protected $fillable = ['libelle_Type'];

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
