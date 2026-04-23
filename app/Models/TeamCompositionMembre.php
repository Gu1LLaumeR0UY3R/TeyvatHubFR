<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id_membre
 * @property int $fid_team
 * @property int $fid_perso
 * @property int $slot
 * @property string|null $role_override
 */
class TeamCompositionMembre extends Model
{
    protected $table      = 'team_composition_membre';
    protected $primaryKey = 'id_membre';
    public    $timestamps = false;

    protected $fillable = [
        'fid_team',
        'fid_perso',
        'slot',
        'role_override',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamComposition::class, 'fid_team', 'id_team');
    }

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso')
                    ->with(['element', 'etoile', 'photos']);
    }
}
