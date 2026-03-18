<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aptitude extends Model
{
    public $timestamps = false;
    protected $table = 'aptitude';
    protected $primaryKey = 'id_aptitude';

    protected $fillable = ['titre_apti', 'descri_apti', 'lvl_apt', 'sub_Apt', 'fid_TypeApti', 'fid_perso'];

    public function typeApti(): BelongsTo
    {
        return $this->belongsTo(TypeApti::class, 'fid_TypeApti', 'id_TypeApti');
    }

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }
}
