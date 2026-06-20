<?php

namespace App\Models\Catalog;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A user's catalog cart (one per user). Holds catalog line items; pricing is
 * derived live from the items so the cart always reflects current catalog
 * prices. Snapshots only happen at checkout.
 */
class CatalogCart extends Model
{
    protected $table = 'catalog_carts';

    protected $fillable = ['user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CatalogCartItem::class);
    }

    /** Race-safe single cart for the current user. */
    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId]);
    }

    /**
     * Live subtotal across items. Requires `items.variant`/`items.product`
     * loaded for efficiency, but works lazily too.
     */
    public function subtotal(): string
    {
        $sum = $this->items->sum(fn (CatalogCartItem $item) => (float) $item->lineTotal());

        return number_format($sum, 2, '.', '');
    }

    public function totalQuantity(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
