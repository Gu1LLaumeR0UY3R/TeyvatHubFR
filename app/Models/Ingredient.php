<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Ingredient extends Model
{
    public $timestamps = false;
    protected $table = 'ingrédient';
    protected $primaryKey = 'id_ingredient';

    protected $fillable = ['nom_ingre', 'slug'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->nom_ingre);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function animaux(): BelongsToMany
    {
        return $this->belongsToMany(Animal::class, 'animal_ingredient', 'fid_ingredient', 'fid_animal');
    }

    public function plats(): BelongsToMany
    {
        return $this->belongsToMany(Plat::class, 'plat_ingredient', 'fid_ingredient', 'fid_plat')
                    ->withPivot('quantite');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
