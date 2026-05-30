<?php

namespace App\Models;

use App\Enums;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property \App\Enums\CheckoutOrderStatus $status
 * @property numeric $local_shipping
 * @property numeric $tax
 * @property numeric $discount
 * @property numeric $shipping
 * @property numeric $sub_total
 * @property numeric $grand_total
 * @property \App\Enums\PaymentMethod $payment_method
 * @property string|null $payment_reference
 * @property int $address_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Address $address
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereLocalShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder wherePaymentReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutOrder whereUserId($value)
 * @mixin \Eloquent
 */
class CheckoutOrder extends Model
{
    protected $fillable = [
        'code',
        'user_id',
        'address_id',
        'sub_total',
        'tax',
        'local_shipping',
        'discount',
        'shipping',
        'grand_total',
        'payment_method',
        'payment_reference',
        'status',
        'total_quantity',
        'address',
    ];

    protected $hidden = [];

    protected $casts = [
        'payment_method' => Enums\PaymentMethod::class,
        'status' => Enums\CheckoutOrderStatus::class,
        'payment_reference' => 'array',
        'address' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public static function generateCode()
    {
        return Order::generateCode();
    }
}
