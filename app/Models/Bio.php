<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bio extends Model
{
    public $timestamps = false;
    protected $table = 'bio';
    protected $primaryKey = 'id_bio';

    protected $fillable = ['titre_bio', 'descri_bio', 'fid_perso'];

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }
}
