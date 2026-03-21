<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SousRegion extends Model
{
    protected $table = 'sous_region';
    protected $primaryKey = 'id_sous_region';

    protected $fillable = ['nom_sous_region', 'slug', 'description', 'fid_region'];

    public function nation(): BelongsTo
    {
        return $this->belongsTo(Nation::class, 'fid_region', 'id_region');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
