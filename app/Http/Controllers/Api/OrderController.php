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
use App\Modules\Payment\Payment;
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

            $paymentMethod = $request->payment_method;
            $payment = Payment::handle($paymentMethod);
            $rules = array_merge($payment->rules(), $rules);

            $validated = $request->validate($rules);
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


            $paymentData = $payment->pay([
                'bank_name' => $request->bank_name,
                'bank_id' => $request->bank_id,
                'iban' => $request->iban,
                'image' => $request->file('image'),
            ]);

            $checkoutOrder = CheckoutOrder::create([
                'user_id' => auth()->id(),
                'address_id' => $validated['address_id'],
                'sub_total' => $carts->sum('subtotal'),
                'tax' => $carts->sum('tax'),
                'shipping' => $carts->sum('shipping'),
                'discount' => $carts->sum('discount'),
                'local_shipping' => $carts->sum('local_shipping'),
                'grand_total' => $carts->sum('total'),
                'payment_method' => $paymentMethod,
                'payment_reference' => json_encode($paymentData),
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
