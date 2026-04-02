<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PersonnageVideo extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'personnage_video';
    protected $primaryKey = 'id_video';

    protected $fillable = ['fid_perso', 'url_video', 'ordre'];

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'fid_perso', 'id_perso');
    }
}

