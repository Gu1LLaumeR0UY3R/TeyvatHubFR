<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TypeAnimal extends Model
{
    public $timestamps = false;
    protected $table = 'type_animal';
    protected $primaryKey = 'id_TAnimal';

    protected $fillable = ['libelle_TAnimal'];

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
