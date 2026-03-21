<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Ennemi extends Model
{
    public $timestamps = false;
    protected $table = 'ennemi';
    protected $primaryKey = 'id_ennemi';

    protected $fillable = ['nom_ennemi', 'slug', 'descri_enn', 'fid_typeEnne', 'fid_element'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->nom_ennemi);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function typeEnnemi(): BelongsTo
    {
        return $this->belongsTo(TypeEnnemi::class, 'fid_typeEnne', 'id_typeEnnemi');
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(Elements::class, 'fid_element', 'id_element');
    }

    public function nations(): BelongsToMany
    {
        return $this->belongsToMany(Nation::class, 'ennemi_region', 'fid_ennemi', 'fid_region');
    }

    public function materiaux(): BelongsToMany
    {
        return $this->belongsToMany(Materiaux::class, 'mate_ennemi', 'fid_ennemi', 'fid_materiaux');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
