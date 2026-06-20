<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Enums\CatalogOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\CatalogOrderResource;
use App\Models\Address;
use App\Models\Catalog\CatalogCart;
use App\Models\Catalog\CatalogOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $data = $request->validate([
            'address_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

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

        $order = DB::transaction(function () use ($items, $address, $data, $cart) {
            $subtotal = $items->sum(fn ($item) => (float) $item->lineTotal());

            $order = CatalogOrder::create([
                'user_id' => user()->id,
                'code' => CatalogOrder::generateCode(),
                'address_id' => $address->id,
                'address_raw' => $address->only(['address_one', 'phone', 'latitude', 'longitude']),
                'status' => CatalogOrderStatus::PENDING,
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'total' => number_format($subtotal, 2, '.', ''),
                'total_quantity' => (int) $items->sum('quantity'),
                'note' => $data['note'] ?? null,
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

        $order->load('items.product.images');

        return response()->json([
            'message' => 'تم إنشاء الطلب بنجاح',
            'order' => new CatalogOrderResource($order),
        ], 201);
    }
}
