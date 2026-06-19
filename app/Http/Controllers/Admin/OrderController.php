<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\Customer\ChangeOrderStatusNotify;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()
            ->with([
                'platform:id,name,logo,currency_symbol',
                'user:id,name,email,phone',
                'items',
                'checkout_order:id,code',
            ])
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

        if ($request->filled('platform_id')) {
            $query->where('platform_id', $request->integer('platform_id'));
        }

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $query->paginate(30)->withQueryString(),
            'statuses' => collect(OrderStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __($s->value),
                'next' => $s->next()?->value,
            ])->values(),
            'filters' => $request->only(['search', 'status', 'platform_id']),
        ]);
    }

    public function products(Order $order): JsonResponse
    {
        $order->load([
            'items.product:id,name,image,price',
            'platform:id,name,currency_symbol',
        ]);

        return response()->json([
            'order' => [
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
            ],
        ]);
    }

    public function nextStatus(Order $order): RedirectResponse
    {
        $next = $order->status->next();

        if ($next === null) {
            return back()->with('error', __('No further status transition is available.'));
        }

        $history = $order->status_history ?? [];
        $history[] = [
            'status' => $next->value,
            'created_at' => now()->toDateTimeString(),
        ];

        $order->update([
            'status' => $next,
            'status_history' => $history,
        ]);

        try {
            $order->checkout_order?->user?->increment('notification_badges');
        } catch (\Throwable $e) {
            Log::info($e->getMessage());
        }

        try {
            $user = $order->checkout_order?->user;
            if ($user) {
                $user->notify(new ChangeOrderStatusNotify(
                    order: $order,
                    title: __('Order Status Changed'),
                    description: $next->message($order->platform),
                    url: '/orders/' . $order->id
                ));
            }
        } catch (\Throwable $e) {
            Log::info($e->getMessage());
        }

        return back()->with('success', __('Status updated to :status', ['status' => __($next->value)]));
    }
}
