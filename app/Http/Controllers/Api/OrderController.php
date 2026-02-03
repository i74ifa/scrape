<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\CheckoutOrder;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Validation\Rules\Enum;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Validation\ValidationException;


class OrderController extends Controller
{
    public function index()
    {
        // get user orders
        $user = auth()->user();
        $orders = $user->orders()->with('items')->paginate(10);

        return OrderResource::collection($orders);
    }

    public function checkout(Request $request)
    {
        try {
            $validated = $request->validate([
                'cart_ids' => 'required|array',
                'cart_ids.*' => 'exists:carts,id',
                'payment_method' => ['required', new Enum(PaymentMethod::class)],
                'payment_reference' => 'required',
                'address_id' => 'required|exists:addresses,id',
            ]);

            $paymentMethod = PaymentMethod::from($validated['payment_method']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }


        $carts = Cart::where('user_id', auth()->id())->whereIn('id', $validated['cart_ids'])->get();

        if (!$carts) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }

        $checkoutOrder = CheckoutOrder::create([
            'user_id' => auth()->id(),
            'address_id' => $validated['address_id'],
            'sub_total' => $carts->sum('subtotal'),
            'tax' => $carts->sum('tax'),
            'shipping' => $carts->sum('shipping'),
            'discount' => $carts->sum('discount'),
            'local_shipping' => $carts->sum('local_shipping'),
            'grand_total' => $carts->sum('total'),
            'payment_method' => PaymentMethod::CASH_ON_DELIVERY->value,
            'payment_status' => PaymentStatus::PENDING->value,
            'payment_reference' => $validated['payment_reference'],
        ]);

        $subtotal = 0;
        $tax = 0;
        $shipping = 0;
        $local_shipping = 0;
        $total = 0;
        $orderIds = [];
        foreach ($carts as $cart) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'cart_id' => $cart->id,
                'total' => $cart->total,
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'shipping' => $cart->shipping,
                'local_shipping' => $cart->local_shipping,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            $subtotal += $cart->subtotal;
            $tax += $cart->tax;
            $shipping += $cart->shipping;
            $local_shipping += $cart->local_shipping;
            $total += $cart->total;

            $cart->delete();

            $orderIds[] = $order->id;
        }

        return response()->json([
            'message' => 'Order created successfully',
            'order' => new OrderResource($order),
        ]);
    }
}
