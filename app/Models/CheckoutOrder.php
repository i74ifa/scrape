<?php

namespace App\Models;

use App\Enums;
use Illuminate\Database\Eloquent\Model;

class CheckoutOrder extends Model
{
    protected $fillable = [
        'user_id',
        'address_id',
        'sub_total',
        'tax',
        'local_shipping',
        'discount',
        'shipping',
        'grand_total',
        'payment_method',
        'payment_status',
        'payment_reference',
    ];

    protected $casts = [
        'payment_method' => Enums\PaymentMethod::class,
        'payment_status' => Enums\PaymentStatus::class,
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
}
