<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Chronologie extends Model
{
    protected $table = 'chronologie';
    protected $primaryKey = 'id_chrono';

    protected $fillable = ['titre', 'resume', 'periode', 'ordre', 'fid_region'];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'fid_region', 'id_region');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
