<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Artefact extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'artefact';
    protected $primaryKey = 'id_artefact';

    protected $fillable = ['nom_artefact', 'slug', 'bonus_2p', 'bonus_4p', 'fid_rareté'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->nom_artefact);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function rareté(): BelongsTo
    {
        return $this->belongsTo(Rarete::class, 'fid_rareté', 'id_rareté');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
