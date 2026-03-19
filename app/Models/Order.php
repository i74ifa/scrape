<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;

/**
 * @property int $id
 * @property string $code
 * @property numeric $local_shipping
 * @property numeric $tax
 * @property numeric $discount
 * @property numeric $shipping
 * @property numeric $sub_total
 * @property numeric $grand_total
 * @property OrderStatus $status
 * @property int $platform_id
 * @property int $checkout_order_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Address|null $address
 * @property-read \App\Models\CheckoutOrder $checkout_order
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Platform $platform
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCheckoutOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereLocalShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePlatformId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    protected $fillable = [
        'code',
        'local_shipping',
        'tax',
        'discount',
        'shipping',
        'sub_total',
        'grand_total',
        'status',
        'payment_method',
        'payment_reference',
        'user_id',
        'platform_id',
        'checkout_order_id',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function checkout_order()
    {
        return $this->belongsTo(CheckoutOrder::class);
    }

    public static function generateCode($prefix = 'ORD')
    {
        // Get current date in YYYYMMDD format
        $dateStr = date("Ymd");

        // Generate a random 3-digit number (001-999)
        $randomNumber = str_pad(rand(1, 99999999), 7, "0", STR_PAD_LEFT);

        // Combine prefix, date, and random number
        $orderCode = "{$prefix}-{$dateStr}-{$randomNumber}";

        if (self::where('code', $orderCode)->exists()) {
            return self::generateCode($prefix);
        }

        return $orderCode;
    }
}
