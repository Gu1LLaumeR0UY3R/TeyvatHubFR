<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Specialite extends Model
{
    public $timestamps = false;
    protected $table = 'spécialité';
    protected $primaryKey = 'id_specialite';

    protected $fillable = ['libelle_spe', 'descri_spe', 'fid_plat', 'fid_perso'];

    public function plat(): BelongsTo
    {
        return $this->belongsTo(Plat::class, 'fid_plat', 'id_plat');
    }

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }
}
