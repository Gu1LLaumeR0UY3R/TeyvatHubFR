<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnageHistoire extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'histoire';
    protected $primaryKey = 'id_histoire';

    protected $fillable = [
        'fid_perso',
        'titre_histoire',
        'histoire',
        'ordre',
    ];

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }
}
