<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Personnage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'personnage';
    protected $primaryKey = 'id_perso';

    protected $fillable = [
        'nom_perso', 'slug', 'affinite_perso',
        'fid_TP', 'fid_etoile', 'fid_element', 'fid_TArmes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->nom_perso);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(Elements::class, 'fid_element', 'id_element');
    }

    public function etoile(): BelongsTo
    {
        return $this->belongsTo(Etoile::class, 'fid_etoile', 'id_etoile');
    }

    public function typePerso(): BelongsTo
    {
        return $this->belongsTo(TypePerso::class, 'fid_TP', 'id_TP');
    }

    public function typeArme(): BelongsTo
    {
        return $this->belongsTo(TypeArme::class, 'fid_TArmes', 'id_TArmes');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
