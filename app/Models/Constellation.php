<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Constellation extends Model
{
    public $timestamps = false;
    protected $table = 'constellation';
    protected $primaryKey = 'id_const';

    protected $fillable = ['titre_const', 'descri_const', 'fid_perso', 'positions_const'];

    protected $casts = [
        'positions_const' => 'array',
    ];

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }

    public function photo(): MorphOne
    {
        return $this->morphOne(Photo::class, 'photoable', 'photoable_type', 'photoable_id', 'id_const');
    }
}
