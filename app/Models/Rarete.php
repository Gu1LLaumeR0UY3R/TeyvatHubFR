<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rarete extends Model
{
    public $timestamps = false;
    protected $table = 'rareté';
    protected $primaryKey = 'id_rareté';

    protected $fillable = ['libelle_rareté'];
}
