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

    public function updateSummary()
    {
        $this->subtotal = $this->carts->sum('subtotal');
        $this->tax = $this->carts->sum('tax');
        $this->shipping = $this->carts->sum('shipping');
        $this->discount = $this->carts->sum('discount');
        $this->local_shipping = $this->carts->sum('local_shipping');
        $this->total = $this->carts->sum('total');

        $this->save();
    }
}
