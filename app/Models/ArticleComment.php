<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleComment extends Model
{
    protected $table = 'article_comments';

    protected $fillable = [
        'article_id',
        'user_id',
        'author_name',
        'content',
        'status',
        'ip_address',
    ];

    // ── Relations ─────────────────────────────────────────────────────

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────

    /** Retourne le pseudo d'affichage (pseudo joueur ou nom dénormalisé). */
    public function getDisplayNameAttribute(): string
    {
        return $this->user?->pseudo ?? $this->author_name ?? 'Anonyme';
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeApproved(Builder $q): Builder
    {
        return $q->where('status', 'approved');
    }

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', 'pending');
    }

    public function scopeRejected(Builder $q): Builder
    {
        return $q->where('status', 'rejected');
    }
}
