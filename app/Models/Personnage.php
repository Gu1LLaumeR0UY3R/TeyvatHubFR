<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'arme_icon',
        'background_actif', 'block_order',
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

    public function bio(): HasOne
    {
        return $this->hasOne(Bio::class, 'fid_perso', 'id_perso');
    }

    public function aptitudes(): HasMany
    {
        return $this->hasMany(Aptitude::class, 'fid_perso', 'id_perso');
    }

    public function constellations(): HasMany
    {
        return $this->hasMany(Constellation::class, 'fid_perso', 'id_perso')->orderBy('id_const');
    }

    public function specialite(): HasOne
    {
        return $this->hasOne(Specialite::class, 'fid_perso', 'id_perso');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'personnage_role', 'fid_perso', 'fid_role');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(PersonnageVideo::class, 'fid_perso', 'id_perso')->orderBy('ordre');
    }

    public function armesRecommandees(): HasMany
    {
        return $this->hasMany(PersonnageArmeRecommandee::class, 'fid_perso', 'id_perso')->orderBy('position');
    }

    public function artefactsRecommandees(): HasMany
    {
        return $this->hasMany(PersonnageArtefactRecommandee::class, 'fid_perso', 'id_perso')->orderBy('position');
    }

    public function nations(): BelongsToMany
    {
        return $this->belongsToMany(Nation::class, 'personnage_nation', 'fid_perso', 'fid_nation');
    }

    public function teamCompositions(): HasMany
    {
        return $this->hasMany(TeamComposition::class, 'fid_perso', 'id_perso')->orderBy('id_team');
    }

    /** Résout l'URL d'une photo à partir de son objet. */
    private function resolvePhotoUrl(Photo $photo): string
    {
        if ($photo->source_url) {
            return $photo->source_url;
        }
        if (filter_var($photo->chemin_photo, FILTER_VALIDATE_URL)) {
            return $photo->chemin_photo;
        }
        return \Illuminate\Support\Facades\Storage::url($photo->chemin_photo);
    }

    /** Icône ronde (carte + grille). */
    public function getIconeUrlAttribute(): string
    {
        $photo = $this->photos->where('type', 'icone')->first()
                 ?? $this->photos->whereNull('type')->first()
                 ?? $this->photos->first();
        if (!$photo) {
            return asset('images/placeholder.webp');
        }
        return $this->resolvePhotoUrl($photo);
    }

    /** Portrait plein (page détail). */
    public function getPortraitUrlAttribute(): string
    {
        $photo = $this->photos->where('type', 'portrait')->first()
                 ?? $this->photos->where('type', 'icone')->first()
                 ?? $this->photos->first();
        if (!$photo) {
            return asset('images/placeholder.webp');
        }
        return $this->resolvePhotoUrl($photo);
    }

    public function getFullImageUrlAttribute(): string
    {
        return $this->portrait_url;
    }
}
