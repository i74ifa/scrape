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

    public function products(CheckoutOrder $checkoutOrder): JsonResponse
    {
        $checkoutOrder->load([
            'orders:id,checkout_order_id,code,platform_id',
            'orders.platform:id,name,currency_symbol',
            'orders.items.product:id,name,image,price',
        ]);

        return response()->json([
            'checkoutOrder' => [
                'id' => $checkoutOrder->id,
                'code' => $checkoutOrder->code,
                'orders' => $checkoutOrder->orders->map(fn ($order) => [
                    'id' => $order->id,
                    'code' => $order->code,
                    'currency_symbol' => $order->platform?->currency_symbol,
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
