<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnageArmeRecommandee extends Model
{
    public $timestamps = false;
    protected $table = 'personnage_arme_recommandee';

    protected $fillable = ['fid_perso', 'nom_build', 'fid_arme', 'position', 'origine', 'starter'];
    protected $casts = ['starter' => 'boolean'];

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }

    public function arme(): BelongsTo
    {
        return $this->belongsTo(Arme::class, 'fid_arme', 'id_arme');
    }
}
