<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Photo extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'photo';
    protected $primaryKey = 'id_photo';

    protected $fillable = ['chemin_photo', 'source_url', 'photoable_type', 'photoable_id', 'type'];

    protected static function booted(): void
    {
        static::saving(function (self $photo) {
            $photo->photoable_type = self::normalizeMorphType($photo->photoable_type);
        });
    }

    public static function normalizeMorphType(string $type): string
    {
        $map = Relation::morphMap();

        if ($type === '') {
            return $type;
        }

        if (isset($map[$type])) {
            return $type;
        }

        foreach ($map as $alias => $class) {
            if ($type === $class || ltrim($type, '\\') === ltrim($class, '\\')) {
                return $alias;
            }
        }

        if (str_contains($type, '\\')) {
            $short = class_basename($type);
            $normalized = strtolower($short);
            if (isset($map[$normalized])) {
                return $normalized;
            }
        }

        $normalized = strtolower(class_basename($type));
        if (isset($map[$normalized])) {
            return $normalized;
        }

        return $type;
    }

    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }
}
