<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Snapshot extends Model
{
    public $timestamps = false;

    protected $table = 'snapshots';
    protected $primaryKey = 'id_snapshot';

    protected $fillable = [
        'fid_perso',
        'fid_admin',
        'action_type',
        'action_at',
    ];

    protected function casts(): array
    {
        return [
            'action_at' => 'datetime',
        ];
    }

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'fid_admin', 'id_admin');
    }

    public function modifications(): HasMany
    {
        return $this->hasMany(SnapshotModification::class, 'fid_snapshot', 'id_snapshot')
            ->orderBy('sub_sequence');
    }
}
