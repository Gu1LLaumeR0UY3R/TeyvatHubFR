<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Photo extends Model
{
    public $timestamps = false;

    protected $table = 'photo';
    protected $primaryKey = 'id_photo';

    protected $fillable = ['chemin_photo', 'source_url', 'photoable_type', 'photoable_id'];

    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }
}
