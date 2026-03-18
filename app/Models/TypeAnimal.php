<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeAnimal extends Model
{
    public $timestamps = false;
    protected $table = 'type_animal';
    protected $primaryKey = 'id_TAnimal';

    protected $fillable = ['libelle_TAnimal'];
}
