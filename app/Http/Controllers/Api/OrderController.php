<?php

namespace App\Http\Controllers\Api;

use App\Classes\BankTransfer;
use App\Enums\CheckoutOrderStatus;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\CheckoutOrder;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Validation\Rules\Enum;
use App\Http\Controllers\Controller;
use App\Http\Resources\CheckoutOrderResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class OrderController extends Controller
{
    public function index()
    {
        // get user orders
        $user = auth()->user();
        $orders = $user->orders()->paginate(10);

        return CheckoutOrderResource::collection($orders);
    }

    public function show(CheckoutOrder $order)
    {
        $order->load('orders.platform:id,name,url,logo', 'orders.items:id,order_id,product_id,quantity,price,total', 'orders.items.product:id,name,price,image,weight', 'address:id,address_one,phone,latitude,longitude');

        return CheckoutOrderResource::make($order);
    }

    public function checkout(Request $request)
    {
        try {
            $rules = [
                // 'cart_ids' => 'required|array',
                // 'cart_ids.*' => 'exists:carts,id',
                'payment_method' => ['required', new Enum(PaymentMethod::class)],
                // 'payment_reference' => 'required',
                'address_id' => 'required|exists:addresses,id',
            ];

            $paymentMethod = PaymentMethod::tryFrom($request->payment_method);
            $paymentHandler = $paymentMethod->getHandlerClass();
            $paymentHandlerInstance = $paymentHandler::make($request->all());

            if ($paymentMethod && $handlerClass = $paymentMethod->getHandlerClass()) {
                $rules = array_merge($rules, $handlerClass::rules());
            }

            $validated = $request->validate($rules);

            $paymentMethod = PaymentMethod::from($validated['payment_method']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // $carts = Cart::where('user_id', auth()->id())->whereIn('id', $validated['cart_ids'])->get();
            $carts = Cart::where('user_id', auth()->id())->get();

            if (!$carts) {
                return response()->json([
                    'message' => 'Cart not found',
                ], 404);
            }

            if ($paymentHandler == BankTransfer::class) {
                if ($request->hasFile('image')) {
                    $image = $request->file('image')->store('images', 'public');

                    $paymentHandlerInstance->image = $image;
                }
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
                'payment_method' => $paymentMethod->value,
                'payment_reference' => json_encode($paymentHandlerInstance->getData()),
                'code' => CheckoutOrder::generateCode(),
                'status' => CheckoutOrderStatus::PENDING_PAYMENT,
            ]);


            foreach ($carts as $cart) {
                $order = $checkoutOrder->orders()->create([
                    'cart_id' => $cart->id,
                    'total' => $cart->total,
                    'subtotal' => $cart->subtotal,
                    'tax' => $cart->tax,
                    'shipping' => $cart->shipping,
                    'local_shipping' => $cart->local_shipping,
                    'platform_id' => $cart->platform_id,
                    'code' => Order::generateCode(),
                ]);

                foreach ($cart->items as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->total,
                    ]);
                }

                $cart->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }


        return response()->json([
            'message' => 'Order created successfully',
            'order' => new CheckoutOrderResource($checkoutOrder),
        ]);
    }
}
