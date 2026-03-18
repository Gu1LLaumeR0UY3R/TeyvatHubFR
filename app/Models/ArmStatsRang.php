<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArmStatsRang extends Model
{
    public $timestamps = false;
    protected $table = 'arm_stats_rang';
    protected $primaryKey = 'id_ASR';

    protected $fillable = ['rang_ASR', 'descri_ASR', 'fid_arme'];

    public function arme(): BelongsTo
    {
        return $this->belongsTo(Arme::class, 'fid_arme', 'id_arme');
    }
}
