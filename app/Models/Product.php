<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

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
        'is_active',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function casts()
    {
        return [
            'variants' => 'array',
        ];
    }
}
