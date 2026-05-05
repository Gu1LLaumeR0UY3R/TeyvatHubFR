<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ActivityLog extends Model
{
    public $timestamps = false;
    public $updatedAt = null;

    protected $table = 'activity_logs';

    protected $fillable = [
        'level',
        'action',
        'subject_type',
        'subject_id',
        'user_type',
        'user_id',
        'user_label',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    // ── Couleurs badge par niveau ─────────────────────────────────────

    public function levelBadgeClass(): string
    {
        return match ($this->level) {
            'debug'    => 'bg-gray-500 text-white',
            'info'     => 'bg-blue-600 text-white',
            'notice'   => 'bg-teal-600 text-white',
            'warning'  => 'bg-yellow-500 text-black',
            'error'    => 'bg-red-600 text-white',
            'critical' => 'bg-red-900 text-white',
            default    => 'bg-gray-600 text-white',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeLevel(Builder $q, string $level): Builder
    {
        return $q->where('level', $level);
    }

    public function scopeAction(Builder $q, string $action): Builder
    {
        return $q->where('action', $action);
    }

    public function scopeByUserType(Builder $q, string $type): Builder
    {
        return $q->where('user_type', $type);
    }

    public function scopeRecent(Builder $q): Builder
    {
        return $q->orderByDesc('created_at');
    }
}
