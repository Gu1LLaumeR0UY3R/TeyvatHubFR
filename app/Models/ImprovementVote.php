<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImprovementVote extends Model
{
    use HasUuids;

    public $timestamps = false;
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'improvement_votes';

    protected $fillable = [
        'improvement_id',
        'user_id',
        'voted_at',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
    ];

    public function improvement(): BelongsTo
    {
        return $this->belongsTo(ImprovementMeta::class, 'improvement_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
