<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogSlug extends Model
{
    protected $table = 'blog_slug';
    protected $primaryKey = 'id_blog_slug';

    protected $fillable = ['slug_base'];
}