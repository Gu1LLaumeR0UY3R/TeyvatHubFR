<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypeApti extends Model
{
    public $timestamps = false;
    protected $table = 'type_apti';
    protected $primaryKey = 'id_TypeApti';

    protected $fillable = ['libelle_Apti'];
}
