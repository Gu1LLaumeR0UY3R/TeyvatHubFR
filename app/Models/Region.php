<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Region extends Model
{
    public $timestamps = false;
    protected $table = 'région';
    protected $primaryKey = 'id_region';

    protected $fillable = ['nom_region', 'slug', 'descri_region'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->nom_region);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function sousRegions(): HasMany
    {
        return $this->hasMany(SousRegion::class, 'fid_region', 'id_region');
    }

    public function ennemis(): BelongsToMany
    {
        return $this->belongsToMany(Ennemi::class, 'ennemi_region', 'fid_region', 'fid_ennemi');
    }

    public function animaux(): BelongsToMany
    {
        return $this->belongsToMany(Animal::class, 'animal_region', 'fid_region', 'fid_animal');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
