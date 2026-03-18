<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypePerso extends Model
{
    protected $table = 'type_perso';
    protected $primaryKey = 'id_TP';
    protected $fillable = ['libelle_TP'];
}
