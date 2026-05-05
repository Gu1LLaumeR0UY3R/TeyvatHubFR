<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImprovementMeta extends Model
{
    public $timestamps = false;

    protected $table = 'improvement_meta';

    protected $fillable = [
        'article_id',
        'planning_status',
        'upvotes_count',
    ];

    protected $casts = [
        'upvotes_count' => 'integer',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ImprovementVote::class, 'improvement_id');
    }
}
