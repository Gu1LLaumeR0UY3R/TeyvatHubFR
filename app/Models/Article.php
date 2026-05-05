<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Article extends Model
{
    // PK = 'id' bigint (default Laravel convention)

    protected $fillable = [
        'author_id',
        'type',
        'status',
        'title',
        'content',
        'is_pinned',
        'pinned_until',
        'scheduled_at',
        'published_at',
    ];

    protected $casts = [
        'content'      => 'array',
        'is_pinned'    => 'boolean',
        'pinned_until' => 'datetime',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────────

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id', 'id_admin');
    }

    public function patchNoteMeta(): HasOne
    {
        return $this->hasOne(PatchNoteMeta::class, 'article_id');
    }

    public function improvementMeta(): HasOne
    {
        return $this->hasOne(ImprovementMeta::class, 'article_id');
    }

    public function survey(): HasOne
    {
        return $this->hasOne(Survey::class, 'article_id');
    }

    // ── Scopes locaux ─────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopePinnedFirst(Builder $query): Builder
    {
        return $query->orderByDesc('is_pinned')->orderByDesc('published_at');
    }

    // ── Helpers ───────────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isPinnedActive(): bool
    {
        return $this->is_pinned && ($this->pinned_until === null || $this->pinned_until->isFuture());
    }
}
