<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Constellation extends Model
{
    public $timestamps = false;
    protected $table = 'constellation';
    protected $primaryKey = 'id_const';

    protected $fillable = ['titre_const', 'descri_const', 'fid_perso'];

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }
}
