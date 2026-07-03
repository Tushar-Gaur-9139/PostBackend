<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'name',
        'price',
        'category',
        'sub_category',
        'images'
    ];

    protected $casts = [
        'images' => 'array',
    ];
}
