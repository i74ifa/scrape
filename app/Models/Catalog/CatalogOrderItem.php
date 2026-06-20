<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An immutable line in a placed catalog order. name/variant_label/unit_price are
 * snapshots; the product/variant FKs are best-effort links (nullOnDelete).
 */
class CatalogOrderItem extends Model
{
    protected $table = 'catalog_order_items';

    protected $fillable = [
        'catalog_order_id', 'catalog_product_id', 'product_variant_id',
        'name', 'variant_label', 'unit_price', 'quantity', 'total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CatalogOrder::class, 'catalog_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'catalog_product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
