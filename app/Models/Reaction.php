<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Reaction extends Model
{
    protected $table = 'reaction';

    protected $primaryKey = 'id_reaction';

    protected $fillable = ['nom_reaction', 'slug'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->nom_reaction);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
