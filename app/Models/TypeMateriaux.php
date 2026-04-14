<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TypeMateriaux extends Model
{
    public $timestamps = false;
    protected $table = 'type_materiaux';
    protected $primaryKey = 'id_typeM';

    protected $fillable = ['libelle_TypeM'];

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
