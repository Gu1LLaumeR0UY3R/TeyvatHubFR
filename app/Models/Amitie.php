<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Amitie extends Model
{
    protected $table = 'amitie';
    protected $primaryKey = 'id_amitie';

    const UPDATED_AT = null;

    protected $fillable = ['fid_demandeur', 'fid_receveur', 'statut'];

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'fid_demandeur');
    }

    public function receveur(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'fid_receveur');
    }
}
