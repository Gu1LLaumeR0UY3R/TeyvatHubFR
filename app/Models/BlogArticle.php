<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogArticle extends Model
{
    protected $table = 'blog_article';
    protected $primaryKey = 'id_article';

    protected $fillable = [
        'titre_article',
        'slug',
        'extrait',
        'contenu_article',
        'statut',
        'date_publication',
    ];

    protected $casts = [
        'date_publication' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $article): void {
            if (blank($article->slug) && filled($article->titre_article)) {
                $article->slug = Str::slug($article->titre_article);
            }
        });

        static::updating(function (self $article): void {
            if ($article->isDirty('titre_article') && blank($article->slug)) {
                $article->slug = Str::slug($article->titre_article);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
