<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArmStatsNiveau extends Model
{
    public $timestamps = false;
    protected $table = 'arm_stats_niveau';
    protected $primaryKey = 'id_ASN';

    protected $fillable = ['lvl_ASN', 'main_stat', 'subs_stats', 'fid_arme'];

    public function arme(): BelongsTo
    {
        return $this->belongsTo(Arme::class, 'fid_arme', 'id_arme');
    }
}
