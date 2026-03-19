<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $image
 * @property numeric $price
 * @property array<array-key, mixed>|null $variants
 * @property string|null $description
 * @property string $url
 * @property int $weight
 * @property string|null $brand
 * @property string|null $category
 * @property int $platform_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_active
 * @property-read \App\Models\Platform $platform
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePlatformId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVariants($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withoutTrashed()
 * @mixin \Eloquent
 */
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
