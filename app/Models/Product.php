<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = [
        'name',
        'image',
        'price',
        'variants',
        'description',
        'url',
        'weight',
        'brand',
        'category',
        'platform_id',
        'user_id',
    ];

    public function casts()
    {
        return [
            'variants' => 'array',
        ];
    }
}
