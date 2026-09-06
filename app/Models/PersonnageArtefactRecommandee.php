<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnageArtefactRecommandee extends Model
{
    public $timestamps = false;
    protected $table = 'personnage_artefact_recommandee';
    protected $primaryKey = 'id_build';

    protected $fillable = [
        'fid_perso',
        'nom_build',
        'fid_artefact_1',
        'pieces_1',
        'fid_artefact_2',
        'pieces_2',
        'main_stat_sablier',
        'main_stat_gobelet',
        'main_stat_couronne',
        'sub_stats',
        'commentaire',
        'position',
    ];

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }

    public function artefact1(): BelongsTo
    {
        return $this->belongsTo(Artefact::class, 'fid_artefact_1', 'id_artefact');
    }

    public function artefact2(): BelongsTo
    {
        return $this->belongsTo(Artefact::class, 'fid_artefact_2', 'id_artefact');
    }
}
