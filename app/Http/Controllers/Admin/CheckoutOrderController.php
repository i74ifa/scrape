<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CheckoutOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\CheckoutOrder;
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
            ])->values(),
            'filters' => $request->only(['search', 'status', 'payment_method']),
        ]);
    }
}
