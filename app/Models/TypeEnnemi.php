<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeEnnemi extends Model
{
    public $timestamps = false;
    protected $table = 'type_ennemi';
    protected $primaryKey = 'id_typeEnnemi';

    protected $fillable = ['libelle_Type'];
}
