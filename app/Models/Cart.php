<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'platform_id',
        'subtotal',
        'tax',
        'shipping',
        'total',
        'local_shipping',
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
                'local_shipping' => 0,
            ]);
        }

        return $cart;
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
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
        $this->total = $this->subtotal + $this->tax + $this->shipping + $this->local_shipping;
        $this->save();
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
}
