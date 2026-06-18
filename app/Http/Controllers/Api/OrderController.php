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
use App\Models\Address;
use App\Models\CartBundle;
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
            $request->validate([
                'payment_method' => ['required', new Enum(PaymentMethod::class)],
            ]);

            $payment = Payment::handle($request->payment_method);

            $validated = $request->validate($payment->rules());
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $cartBundle = CartBundle::getActiveCartBundle();

            $address = Address::find($cartBundle->address_id);
            if (!$address) {
                $address = user()->addresses()->where('is_default', true)->first();
            }
            // use default if not found


            if (!$address) {
                return response()->json([
                    'message' => __('Address not found'),
                ], 404);
            }

            $carts = $cartBundle->carts()->with('items')->get();

            if ($carts->isEmpty()) {
                return response()->json([
                    'message' => __('Cart not found'),
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
                'address_id' => $cartBundle->address_id,
                'address_raw' => $address ? $address->only(['address_one', 'phone', 'latitude', 'longitude']) : null,
                'sub_total' => $cartBundle->subtotal,
                'tax' => $cartBundle->tax,
                'shipping' => $cartBundle->shipping,
                'discount' => $cartBundle->discount,
                'local_shipping' => $cartBundle->local_shipping,
                'grand_total' => $cartBundle->total,
                'payment_method' => $request->payment_method,
                'payment_reference' => json_encode($paymentData),
                'code' => CheckoutOrder::generateCode(),
                'status' => CheckoutOrderStatus::PENDING_PAYMENT,
            ]);


            foreach ($carts as $cart) {
                $order = $checkoutOrder->orders()->create([
                    'grand_total' => $cart->total,
                    'sub_total' => $cart->subtotal,
                    'tax' => $cart->tax,
                    'shipping' => $cart->shipping,
                    'discount' => $cart->discount,
                    'platform_id' => $cart->platform_id,
                    'total_quantity' => $cart->items->sum('quantity'),
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

            $cartBundle->delete();

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
