<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnapshotModification extends Model
{
    public $timestamps = false;

    protected $table = 'snapshot_modifications';
    protected $primaryKey = 'id_snapshot_modification';

    protected $fillable = [
        'fid_snapshot',
        'sub_sequence',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class, 'fid_snapshot', 'id_snapshot');
    }
}
