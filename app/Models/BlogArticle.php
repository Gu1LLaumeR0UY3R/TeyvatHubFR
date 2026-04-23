<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class BlogArticle extends Model
{
    use HasFactory;

    public const EXCERPT_LENGTH = 170;

    protected $table = 'blog_article';
    protected $primaryKey = 'id_article';

    protected $fillable = [
        'titre_article',
        'slug',
        'extrait',
        'layout_json',
        'statut',
        'date_publication',
    ];

    protected $casts = [
        'date_publication' => 'datetime',
        'layout_json' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $article): void {
            $article->syncExcerpt();

            if (blank($article->slug) && filled($article->titre_article)) {
                $article->slug = Str::slug($article->titre_article);
            }
        });

        static::updating(function (self $article): void {
            $article->syncExcerpt();

            if ($article->isDirty('titre_article') && blank($article->slug)) {
                $article->slug = Str::slug($article->titre_article);
            }
        });
    }

    public function getExtraitAttribute($value): ?string
    {
        if (filled($value)) {
            return $value;
        }

        return self::makeExcerpt($this->textFromLayout($this->layout_json));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }

    public function featuredPhotos(): MorphMany
    {
        return $this->photos()->where('type', 'featured')->orderBy('id_photo');
    }

    public function inlinePhotos(): MorphMany
    {
        return $this->photos()->where('type', 'inline')->orderBy('id_photo');
    }

    public function resolvePhotoUrl(?Photo $photo): ?string
    {
        if (!$photo) {
            return null;
        }

        if (filled($photo->source_url)) {
            return $photo->source_url;
        }

        if (filter_var((string) $photo->chemin_photo, FILTER_VALIDATE_URL)) {
            return $photo->chemin_photo;
        }

        return asset('storage/' . ltrim((string) $photo->chemin_photo, '/'));
    }

    public static function makeExcerpt(?string $content): ?string
    {
        $plainText = trim(strip_tags((string) $content));

        if ($plainText === '') {
            return null;
        }

        return Str::limit($plainText, self::EXCERPT_LENGTH);
    }

    public function syncExcerpt(): void
    {
        $this->attributes['extrait'] = self::makeExcerpt($this->textFromLayout($this->layout_json));
    }

    public static function textFromLayout(array|string|null $layout): string
    {
        if (is_string($layout)) {
            $decoded = json_decode($layout, true);
            $layout = is_array($decoded) ? $decoded : null;
        }

        if (!is_array($layout)) {
            return '';
        }

        $blocks = $layout['blocks'] ?? [];
        if (!is_array($blocks)) {
            return '';
        }

        return collect($blocks)
            ->filter(fn($block) => is_array($block) && in_array(($block['type'] ?? null), ['heading', 'text'], true))
            ->map(fn($block) => trim((string) ($block['text'] ?? '')))
            ->filter()
            ->implode("\n\n");
    }
}
