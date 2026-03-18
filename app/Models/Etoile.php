<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Etoile extends Model
{
    public $timestamps = false;

    protected $table = 'etoile';
    protected $primaryKey = 'id_etoile';
    protected $fillable = ['libelle'];

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
