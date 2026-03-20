<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property numeric $subtotal
 * @property numeric $tax
 * @property numeric $shipping
 * @property numeric $local_shipping
 * @property numeric $total
 * @property numeric $discount
 * @property int|null $address_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cart> $carts
 * @property-read int|null $carts_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle whereAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle whereLocalShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle whereShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartBundle whereUserId($value)
 * @mixin \Eloquent
 */
class CartBundle extends Model
{
    protected $fillable = [
        'user_id',
        'subtotal',
        'tax',
        'shipping',
        'discount',
        'local_shipping',
        'total',
        'address_id',
    ];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public static function getActiveCartBundle()
    {
        $defaultAddress = user()->addresses()->where('is_default', true)->first();

        $localShippingCost = 0;
        if ($defaultAddress) {
            $localShippingCost = $defaultAddress?->state?->delivery_cost ?? 0;
        }

        return self::where('user_id', user('id'))->firstOrCreate([
            'user_id' => user('id'),
        ], [
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'local_shipping' => $localShippingCost,
            'total' => 0,
            'address_id' => $defaultAddress?->id,
            'user_id' => user('id'),
            'discount' => 0,
        ]);
    }

    public function updateSummary()
    {
        $this->subtotal = $this->carts->sum('subtotal');
        $this->tax = $this->carts->sum('tax');
        $this->shipping = $this->carts->sum('shipping');
        $this->discount = $this->carts->sum('discount');
        $this->total = $this->carts->sum('total');

        $this->save();
    }
}
