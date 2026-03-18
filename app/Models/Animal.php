<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Animal extends Model
{
    public $timestamps = false;
    protected $table = 'animaux';
    protected $primaryKey = 'id_animal';

    protected $fillable = ['nom_animal', 'slug', 'descri_animal', 'fid_TAnimal'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->nom_animal);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function typeAnimal(): BelongsTo
    {
        return $this->belongsTo(TypeAnimal::class, 'fid_TAnimal', 'id_TAnimal');
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'animal_region', 'fid_animal', 'fid_region');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'animal_ingredient', 'fid_animal', 'fid_ingredient');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
