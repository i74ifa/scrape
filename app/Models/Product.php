<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public const DEFAULT_WEIGHT_GRAMS = 250;

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
        'product_id',
    ];

    public function casts()
    {
        return [
            'variants' => 'array',
        ];
    }
}
