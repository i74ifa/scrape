<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property numeric $total
 * @property numeric $subtotal
 * @property numeric $tax
 * @property numeric $shipping
 * @property numeric $discount
 * @property int $is_delivery_to_home
 * @property int $platform_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $cart_bundle_id
 * @property-read \App\Models\Address|null $address
 * @property-read \App\Models\CartBundle|null $cart_bundle
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Platform $platform
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCartBundleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereIsDeliveryToHome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereLocalShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart wherePlatformId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUserId($value)
 * @mixin \Eloquent
 */
class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'platform_id',
        'subtotal',
        'tax',
        'shipping',
        'total',
        'cart_bundle_id',
        'local_shipping',
        'discount'
    ];

    public static function getCart($platformId): self
    {
        $cart = self::where('user_id', auth()->user()->id)->where('platform_id', $platformId)->first();

        if (!$cart) {
            $cart = self::create([
                'user_id' => auth()->user()->id,
                'platform_id' => $platformId,
                'subtotal' => 0,
                'tax' => 0,
                'shipping' => 0,
                'total' => 0,
            ]);
        }

        return $cart;
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function updateSummary()
    {
        $items = $this->items;

        foreach ($items as $item) {
            $item->price = $item->product->price;
            $item->total = $item->product->price * $item->quantity;
            $item->save();
        }

        $this->subtotal = $this->items->sum('total');
        // 5% tax
        $this->tax = $this->subtotal * 0.05;
        $this->shipping = $this->items->sum('shipping');
        $this->total = $this->subtotal + $this->tax + $this->shipping;
        $this->save();

        if ($this->subtotal === 0 || $this->items->count() === 0) {
            $this->items()->delete();
            $this->delete();
        }

        // update cartBundle
        $this->cart_bundle->updateSummary();
    }

    public function cart_bundle()
    {
        return $this->belongsTo(CartBundle::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
}
