<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //laravel acepte que se guarde el titulo del post
    protected $fillable = ['title'];
}
