<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    public $timestamps = false;
    protected $table = 'role';
    protected $primaryKey = 'id_role';

    protected $fillable = ['libelle_role', 'descri_role'];

    public function personnages(): BelongsToMany
    {
        return $this->belongsToMany(Personnage::class, 'personnage_role', 'fid_role', 'fid_perso');
    }
}
