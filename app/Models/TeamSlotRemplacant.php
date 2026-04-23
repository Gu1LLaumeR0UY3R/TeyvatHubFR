<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id_rpl
 * @property int $fid_team
 * @property int $slot
 * @property int $fid_perso_remplacant
 * @property string|null $role_override
 */
class TeamSlotRemplacant extends Model
{
    protected $table      = 'team_slot_remplacant';
    protected $primaryKey = 'id_rpl';
    public    $timestamps = false;

    protected $fillable = [
        'fid_team',
        'slot',
        'fid_perso_remplacant',
        'role_override',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamComposition::class, 'fid_team', 'id_team');
    }

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso_remplacant', 'id_perso')
                    ->with(['element', 'photos']);
    }
}
