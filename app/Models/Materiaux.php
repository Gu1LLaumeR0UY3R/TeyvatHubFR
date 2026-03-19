<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Materiaux extends Model
{
    public $timestamps = false;
    protected $table = 'materiaux';
    protected $primaryKey = 'id_materiaux';

    protected $fillable = ['nom_mat', 'slug', 'descri_mat', 'fid_typeM', 'fid_rareté'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->nom_mat);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function typeMateriaux(): BelongsTo
    {
        return $this->belongsTo(TypeMateriaux::class, 'fid_typeM', 'id_typeM');
    }

    public function rarete(): BelongsTo
    {
        return $this->belongsTo(Rarete::class, 'fid_rareté', 'id_rareté');
    }

    public function ennemis(): BelongsToMany
    {
        return $this->belongsToMany(Ennemi::class, 'mate_ennemi', 'fid_materiaux', 'fid_ennemi');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
