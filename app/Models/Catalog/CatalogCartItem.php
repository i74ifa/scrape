<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A catalog cart line: a product + optional variant + quantity. Unit price is
 * derived from the variant (if any) else the product, using effectivePrice()
 * so sale prices apply automatically.
 */
class CatalogCartItem extends Model
{
    protected $table = 'catalog_cart_items';

    protected $fillable = [
        'catalog_cart_id', 'catalog_product_id', 'product_variant_id', 'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(CatalogCart::class, 'catalog_cart_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'catalog_product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /** Current unit price (variant overrides product), 2-decimal string. */
    public function unitPrice(): string
    {
        if ($this->product_variant_id && $this->variant) {
            return $this->variant->effectivePrice();
        }

        return $this->product?->effectivePrice() ?? '0.00';
    }

    /** Current line total = unit price × quantity, 2-decimal string. */
    public function lineTotal(): string
    {
        return number_format((float) $this->unitPrice() * $this->quantity, 2, '.', '');
    }

    /** "أحمر / 128GB" from the variant's option values, or null for a simple item. */
    public function variantLabel(): ?string
    {
        if (! $this->product_variant_id || ! $this->variant) {
            return null;
        }

        $label = $this->variant->attributeValues->pluck('value')->implode(' / ');

        return $label !== '' ? $label : null;
    }
}
