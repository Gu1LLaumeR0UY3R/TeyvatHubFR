<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends Model
{
    public $timestamps = false;

    protected $table = 'survey_questions';

    protected $fillable = [
        'survey_id',
        'type',
        'label',
        'position',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class, 'question_id');
    }
}
