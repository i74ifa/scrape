<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Enums\CatalogOrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\CatalogOrderResource;
use App\Models\Address;
use App\Models\Catalog\CatalogCart;
use App\Models\Catalog\CatalogOrder;
use App\Modules\Payment\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;

/**
 * Customer-facing catalog orders: place an order from the cart, then list/view
 * placed orders. Snapshots the catalog + address at checkout.
 */
class OrderController extends Controller
{
    public function index()
    {
        $orders = CatalogOrder::query()
            ->where('user_id', user()->id)
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return CatalogOrderResource::collection($orders);
    }

    public function show(CatalogOrder $order)
    {
        abort_unless($order->user_id === user()->id, 403);

        $order->load('items.product.images');

        return new CatalogOrderResource($order);
    }

    public function checkout(Request $request)
    {
        // Resolve + validate the payment gateway first (same flow as the scraped
        // Api\OrderController). The chosen method drives both the extra payload
        // rules and the order's initial status.
        $paymentMethod = $request->validate([
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
        ])['payment_method'];

        $payment = Payment::handle($paymentMethod);

        $data = $request->validate(array_merge([
            'address_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], $payment->rules()));

        $cart = CatalogCart::forUser(user()->id)->load([
            'items.product.images',
            'items.variant.attributeValues',
        ]);

        // Only purchasable lines: the product still exists and is active.
        $items = $cart->items->filter(fn ($item) => $item->product && $item->product->is_active);

        if ($items->isEmpty()) {
            return response()->json(['message' => 'السلة فارغة أو تحتوي على منتجات غير متوفرة.'], 422);
        }

        // Resolve the shipping address: the chosen one (must belong to the user),
        // else the user's default. Snapshot it so the order survives later edits.
        $address = null;
        if (! empty($data['address_id'])) {
            $address = Address::where('id', $data['address_id'])
                ->where('user_id', user()->id)
                ->first();
        }
        $address ??= user()->addresses()->where('is_default', true)->first();

        if (! $address) {
            return response()->json(['message' => 'لم يتم العثور على عنوان للشحن.'], 404);
        }

        try {
            $order = DB::transaction(function () use ($items, $address, $data, $cart, $payment, $paymentMethod, $request) {
                $subtotal = $items->sum(fn ($item) => (float) $item->lineTotal());

                // Capture the bank-transfer payload (stores the receipt image, etc.).
                $paymentData = $payment->pay([
                    'bank_name' => $request->input('bank_name'),
                    'bank_id' => $request->input('bank_id'),
                    'iban' => $request->input('iban'),
                    'image' => $request->file('image'),
                ]);

                $order = CatalogOrder::create([
                    'user_id' => user()->id,
                    'code' => CatalogOrder::generateCode(),
                    'address_id' => $address->id,
                    'address_raw' => $address->only(['address_one', 'phone', 'latitude', 'longitude']),
                    // Bank transfers start in payment verification; admin advances
                    // pending_payment → pending once the receipt is confirmed.
                    'status' => $paymentMethod === PaymentMethod::BANKS_TRANSFER->value
                        ? CatalogOrderStatus::PENDING_PAYMENT
                        : CatalogOrderStatus::PENDING,
                    'subtotal' => number_format($subtotal, 2, '.', ''),
                    'total' => number_format($subtotal, 2, '.', ''),
                    'total_quantity' => (int) $items->sum('quantity'),
                    'note' => $data['note'] ?? null,
                    'payment_method' => $paymentMethod,
                    'payment_reference' => $paymentData,
                ]);

                foreach ($items as $item) {
                    $order->items()->create([
                        'catalog_product_id' => $item->catalog_product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'name' => $item->product->name,
                        'variant_label' => $item->variantLabel(),
                        'unit_price' => $item->unitPrice(),
                        'quantity' => $item->quantity,
                        'total' => $item->lineTotal(),
                    ]);
                }

                // Empty the cart once the order is captured.
                $cart->items()->delete();

                return $order;
            });
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => 'تعذّر إنشاء الطلب',
                'error' => $e->getMessage(),
            ], 500);
        }

        $order->load('items.product.images');

        return response()->json([
            'message' => 'تم إنشاء الطلب بنجاح',
            'order' => new CatalogOrderResource($order),
        ], 201);
    }
}
