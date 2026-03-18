<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TypeArme extends Model
{
    protected $table = 'type_armes';
    protected $primaryKey = 'id_TArmes';
    protected $fillable = ['libelle_TArme'];

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
