<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Merchant-authored catalog product. Lives in the `catalog_products` table,
 * separate from the scraped `products` table that powers cart/orders.
 */
class Product extends Model
{
    protected $table = 'catalog_products';

    protected $fillable = [
        'brand_id', 'name', 'name_translations', 'short_description',
        'description', 'description_translations', 'price', 'sale_price',
        'end_discount_date', 'weight', 'sku', 'slug', 'promotion',
        'specifications', 'tags', 'has_variants', 'is_digital', 'is_active',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'specifications' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'end_discount_date' => 'datetime',
        'likes' => 'integer',
        'views' => 'integer',
        'has_variants' => 'boolean',
        'is_digital' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_catalog_product',
            'catalog_product_id',
            'category_id'
        );
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    /**
     * The price a customer actually pays (sale price if set, else list price),
     * as a 2-decimal string to match the API money convention.
     */
    public function effectivePrice(): string
    {
        return number_format((float) ($this->sale_price ?? $this->price), 2, '.', '');
    }

    /**
     * For a variant product, the min/max effective price across its variants.
     * Null for simple products. Requires the `variants` relation loaded.
     *
     * @return array{min: string, max: string}|null
     */
    public function priceRange(): ?array
    {
        if (! $this->has_variants || $this->variants->isEmpty()) {
            return null;
        }

        $prices = $this->variants->map(fn (ProductVariant $v) => (float) $v->effectivePrice());

        return [
            'min' => number_format($prices->min(), 2, '.', ''),
            'max' => number_format($prices->max(), 2, '.', ''),
        ];
    }
}
