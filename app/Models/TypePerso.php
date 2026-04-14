<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TypePerso extends Model
{
    public $timestamps = false;

    protected $table = 'type_perso';
    protected $primaryKey = 'id_TP';
    protected $fillable = ['libelle_TP'];

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
