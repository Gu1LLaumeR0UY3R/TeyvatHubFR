<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Nation extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'nation';
    protected $primaryKey = 'id_region';

    protected $fillable = ['nom_region', 'slug', 'descri_region'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = Schema::hasTable('nation') ? 'nation' : 'région';
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->nom_region);
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

    public function produits(): HasMany
    {
        return $this->hasMany(Produits::class, 'fid_region', 'id_region');
    }

    public function chronologie(): HasMany
    {
        return $this->hasMany(Chronologie::class, 'fid_region', 'id_region')->orderBy('ordre');
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

    public function getFullImageUrlAttribute(): string
    {
        return $this->icone_url;
    }

    public function getIconeUrlAttribute(): string
    {
        $iconPath = "storage/photos/regions/icones/{$this->slug}.png";
        if (file_exists(public_path($iconPath))) {
            return asset($iconPath);
        }
        // Fallback à photo en base de données
        $photo = $this->photos->first();
        if (!$photo) {
            return asset('images/placeholder.webp');
        }
        if ($photo->source_url) {
            return $photo->source_url;
        }
        if (filter_var($photo->chemin_photo, FILTER_VALIDATE_URL)) {
            return $photo->chemin_photo;
        }
        return \Illuminate\Support\Facades\Storage::url($photo->chemin_photo);
    }

    public function getPanoramaUrlAttribute(): string
    {
        $panoramaPath = "storage/photos/regions/régions_panorama/{$this->slug}1.png";
        if (file_exists(public_path($panoramaPath))) {
            return asset($panoramaPath);
        }
        // Fallback: chercher .jpeg
        $panoramaPathJpeg = "storage/photos/regions/régions_panorama/{$this->slug}1.jpeg";
        if (file_exists(public_path($panoramaPathJpeg))) {
            return asset($panoramaPathJpeg);
        }
        return $this->icone_url;
    }
}
