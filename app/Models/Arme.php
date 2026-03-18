<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Arme extends Model
{
    public $timestamps = false;
    protected $table = 'armes';
    protected $primaryKey = 'id_arme';

    protected $fillable = ['nom_arme', 'slug', 'descr_arme', 'nom_competence', 'main_stat_type', 'sub_stat_type', 'fid_TArmes', 'fid_etoile'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->nom_arme);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function typeArme(): BelongsTo
    {
        return $this->belongsTo(TypeArme::class, 'fid_TArmes', 'id_TArmes');
    }

    public function etoile(): BelongsTo
    {
        return $this->belongsTo(Etoile::class, 'fid_etoile', 'id_etoile');
    }

    public function statsNiveaux(): HasMany
    {
        return $this->hasMany(ArmStatsNiveau::class, 'fid_arme', 'id_arme')->orderBy('lvl_ASN');
    }

    public function statsRangs(): HasMany
    {
        return $this->hasMany(ArmStatsRang::class, 'fid_arme', 'id_arme')->orderBy('rang_ASR');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
