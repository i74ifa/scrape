<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;

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
