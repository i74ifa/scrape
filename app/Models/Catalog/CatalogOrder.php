<?php

namespace App\Models\Catalog;

use App\Enums\CatalogOrderStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A placed catalog order. Immutable line items snapshot the catalog at checkout;
 * the address is kept as both an FK and a JSON snapshot.
 */
class CatalogOrder extends Model
{
    protected $table = 'catalog_orders';

    protected $fillable = [
        'user_id', 'code', 'address_id', 'address_raw', 'status',
        'subtotal', 'total', 'total_quantity', 'note',
    ];

    protected $casts = [
        'address_raw' => 'array',
        'status' => CatalogOrderStatus::class,
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'total_quantity' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CatalogOrderItem::class);
    }

    /** Reuse the existing order-code generator with a catalog prefix. */
    public static function generateCode(): string
    {
        return Order::generateCode('CAT');
    }
}
