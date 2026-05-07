<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
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
            ])->values(),
            'filters' => $request->only(['search', 'status', 'platform_id']),
        ]);
    }
}
