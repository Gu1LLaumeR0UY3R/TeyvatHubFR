<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeMateriaux extends Model
{
    public $timestamps = false;
    protected $table = 'type_materiaux';
    protected $primaryKey = 'id_typeM';

    protected $fillable = ['libelle_TypeM'];
}
