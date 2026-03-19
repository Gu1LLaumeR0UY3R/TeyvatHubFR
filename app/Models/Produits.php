<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produits extends Model
{
    public $timestamps = false;
    protected $table = 'produits';
    protected $primaryKey = 'id_produit';

    protected $fillable = ['libelle_produit', 'descri_produit', 'fid_region'];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'fid_region', 'id_region');
    }
}
