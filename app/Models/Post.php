<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    protected $fillable = ['type', 'title', 'summary' ,'content','image' , 'is_published'];

}
