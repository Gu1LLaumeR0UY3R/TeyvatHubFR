<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnageStatsRecommandee extends Model
{
    public $timestamps = false;
    protected $table = 'personnage_stats_recommandee';
    protected $primaryKey = 'id_stats';

    protected $fillable = [
        'fid_perso',
        'nom_build',
        'pv',
        'atq',
        'def',
        'taux_crit',
        'degats_crit',
        'maitrise_elementaire',
        'recharge_energetique',
        'commentaire',
        'position',
    ];

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }
}
