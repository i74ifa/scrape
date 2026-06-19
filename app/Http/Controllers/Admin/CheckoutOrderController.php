<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CheckoutOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\CheckoutOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = CheckoutOrder::query()
            ->with([
                'user:id,name,email,phone',
                'orders:id,checkout_order_id,code,status,grand_total,platform_id',
                'orders.platform:id,name,logo',
            ])
            ->withCount('orders')
            ->latest();

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($u) use ($term) {
                        $u->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->string('payment_method'));
        }

        return Inertia::render('Admin/CheckoutOrders/Index', [
            'checkoutOrders' => $query->paginate(30)->withQueryString(),
            'statuses' => collect(CheckoutOrderStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'next' => $s->next()?->value,
            ])->values(),
            'filters' => $request->only(['search', 'status', 'payment_method']),
        ]);
    }

    public function show(CheckoutOrder $checkoutOrder)
    {
        $checkoutOrder->load([
            'user:id,name,email,phone',
            'address:id,address_one,phone',
            'orders:id,checkout_order_id,code,status,grand_total,sub_total,platform_id',
            'orders.platform:id,name,logo,currency_symbol',
            'orders.items.product:id,name,image,price,url',
        ]);

        return Inertia::render('Admin/CheckoutOrders/Show', [
            'checkoutOrder' => [
                'id' => $checkoutOrder->id,
                'code' => $checkoutOrder->code,
                'status' => $checkoutOrder->status->value,
                'status_label' => $checkoutOrder->status->label(),
                'status_next' => $checkoutOrder->status->next()?->value,
                'payment_method' => $checkoutOrder->payment_method?->value,
                'payment_reference' => $checkoutOrder->payment_reference,
                'sub_total' => $checkoutOrder->sub_total,
                'tax' => $checkoutOrder->tax,
                'local_shipping' => $checkoutOrder->local_shipping,
                'shipping' => $checkoutOrder->shipping,
                'discount' => $checkoutOrder->discount,
                'grand_total' => $checkoutOrder->grand_total,
                'total_quantity' => $checkoutOrder->total_quantity,
                'created_at' => $checkoutOrder->created_at,
                'user' => $checkoutOrder->user ? [
                    'id' => $checkoutOrder->user->id,
                    'name' => $checkoutOrder->user->name,
                    'email' => $checkoutOrder->user->email,
                    'phone' => $checkoutOrder->user->phone,
                ] : null,
                'address' => $checkoutOrder->address ? [
                    'address_one' => $checkoutOrder->address->address_one,
                    'phone' => $checkoutOrder->address->phone,
                ] : $checkoutOrder->address_raw,
                'orders' => $checkoutOrder->orders->map(fn ($order) => [
                    'id' => $order->id,
                    'code' => $order->code,
                    'status' => $order->status->value,
                    'status_next' => $order->status->next()?->value,
                    'grand_total' => $order->grand_total,
                    'sub_total' => $order->sub_total,
                    'currency_symbol' => $order->platform?->currency_symbol,
                    'platform' => $order->platform ? [
                        'id' => $order->platform->id,
                        'name' => $order->platform->name,
                        'logo' => $order->platform->logo,
                    ] : null,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->total,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'image' => $item->product->image,
                            'price' => $item->product->price,
                            'url' => $item->product->url,
                        ] : null,
                    ]),
                ]),
            ],
        ]);
    }

    public function products(CheckoutOrder $checkoutOrder): JsonResponse
    {
        $checkoutOrder->load([
            'orders:id,checkout_order_id,code,status,platform_id',
            'orders.platform:id,name,logo,currency_symbol',
            'orders.items.product:id,name,image,price,url',
        ]);

        return response()->json([
            'checkoutOrder' => [
                'id' => $checkoutOrder->id,
                'code' => $checkoutOrder->code,
                'orders' => $checkoutOrder->orders->map(fn ($order) => [
                    'id' => $order->id,
                    'code' => $order->code,
                    'status' => $order->status->value,
                    'status_next' => $order->status->next()?->value,
                    'currency_symbol' => $order->platform?->currency_symbol,
                    'platform' => $order->platform ? [
                        'id' => $order->platform->id,
                        'name' => $order->platform->name,
                        'logo' => $order->platform->logo,
                    ] : null,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->total,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'image' => $item->product->image,
                            'price' => $item->product->price,
                            'url' => $item->product->url,
                        ] : null,
                    ]),
                ]),
            ],
        ]);
    }

    public function nextStatus(CheckoutOrder $checkoutOrder): RedirectResponse
    {
        $next = $checkoutOrder->status->next();

        if ($next === null) {
            return back()->with('error', __('No further status transition is available.'));
        }

        $checkoutOrder->update(['status' => $next]);

        return back()->with('success', __('Status updated to :status', ['status' => $next->label()]));
    }
}
