<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Plat extends Model
{
    public $timestamps = false;
    protected $table = 'plat';
    protected $primaryKey = 'id_plat';

    protected $fillable = ['nom_plat', 'slug', 'descri_plat', 'fid_rareté'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->nom_plat);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function rarete(): BelongsTo
    {
        return $this->belongsTo(Rarete::class, 'fid_rareté', 'id_rareté');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'plat_ingredient', 'fid_plat', 'fid_ingredient')
                    ->withPivot('quantite');
    }

    public function specialite(): HasOne
    {
        return $this->hasOne(Specialite::class, 'fid_plat', 'id_plat');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
