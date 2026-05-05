<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyAnswer extends Model
{
    use HasUuids;

    public $timestamps = false;
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'survey_answers';

    protected $fillable = [
        'response_id',
        'question_id',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class, 'response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'question_id');
    }
}
