<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatchNoteMeta extends Model
{
    public $timestamps = false;

    protected $table = 'patch_note_meta';

    protected $fillable = [
        'article_id',
        'version',
        'release_date',
        'changelog',
    ];

    protected $casts = [
        'changelog'    => 'array',
        'release_date' => 'date',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
