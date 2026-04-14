<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamComposition extends Model
{
    protected $table      = 'team_composition';
    protected $primaryKey = 'id_team';
    public    $timestamps = true;

    protected $fillable = [
        'fid_perso',
        'type_reaction',
        'tag',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }

    public function membres(): HasMany
    {
        return $this->hasMany(TeamCompositionMembre::class, 'fid_team', 'id_team')
                    ->orderBy('slot');
    }

    public function alternatives(): HasMany
    {
        return $this->hasMany(TeamSlotRemplacant::class, 'fid_team', 'id_team')
                    ->orderBy('slot')
                    ->orderBy('id_rpl');
    }
}
