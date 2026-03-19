<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    public $timestamps = false;
    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    protected $fillable = ['pseudo_admin', 'email_admin', 'mot_de_passe_admin', 'role'];

    protected $hidden = ['mot_de_passe_admin'];
}
