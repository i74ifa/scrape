<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Enums\CatalogOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Catalog\CatalogOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Admin management of catalog orders — list, view, and advance/cancel status.
 */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = CatalogOrder::query()
            ->with('user:id,name,phone')
            ->withCount('items');

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->string('payment_method'));
        }

        $orders = $query->latest()->paginate(20)->withQueryString()
            ->through(fn (CatalogOrder $o) => [
                'id' => $o->id,
                'code' => $o->code,
                'status' => $o->status->value,
                'status_label' => $o->status->label(),
                'status_color' => $o->status->color(),
                'payment_method' => $o->payment_method?->value,
                'payment_method_label' => $o->payment_method?->label(),
                'total' => money($o->total),
                'total_quantity' => $o->total_quantity,
                'items_count' => $o->items_count,
                'customer' => $o->user?->name,
                'phone' => $o->user?->phone,
                'created_at' => $o->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Catalog/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only('search', 'status', 'payment_method'),
            'statuses' => CatalogOrderStatus::toArray(),
            'payment_methods' => \App\Enums\PaymentMethod::all(),
        ]);
    }

    public function show(CatalogOrder $order)
    {
        $order->load('user:id,name,phone,email', 'items.product:id,name', 'address');

        // Resolve the bank-transfer receipt path to a public URL so the admin
        // can preview/verify it. payment_reference is cast to array on the model.
        $paymentReference = $order->payment_reference;
        if (is_array($paymentReference) && ! empty($paymentReference['image'])) {
            $paymentReference['image_url'] = Storage::url($paymentReference['image']);
        }

        return Inertia::render('Admin/Catalog/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'code' => $order->code,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'status_color' => $order->status->color(),
                'next_status' => $order->status->next()?->value,
                'next_status_label' => $order->status->next()?->label(),
                'can_cancel' => $order->status->canCancel(),
                'payment_method' => $order->payment_method?->value,
                'payment_method_label' => $order->payment_method?->label(),
                'payment_reference' => $paymentReference,
                'is_pending_payment' => $order->status === CatalogOrderStatus::PENDING_PAYMENT,
                'subtotal' => money($order->subtotal),
                'total' => money($order->total),
                'total_quantity' => $order->total_quantity,
                'note' => $order->note,
                'address' => $order->address_raw,
                'customer' => [
                    'name' => $order->user?->name,
                    'phone' => $order->user?->phone,
                    'email' => $order->user?->email,
                ],
                'items' => $order->items->map(fn ($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'variant_label' => $i->variant_label,
                    'unit_price' => money($i->unit_price),
                    'quantity' => $i->quantity,
                    'total' => money($i->total),
                ]),
                'created_at' => $order->created_at?->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Move the order to a new status. Only a legal transition is accepted: the
     * single forward step (status->next()) or a cancel from a non-terminal state.
     */
    public function updateStatus(Request $request, CatalogOrder $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(CatalogOrderStatus::values())],
        ]);

        $target = CatalogOrderStatus::from($data['status']);
        $current = $order->status;

        $isForward = $current->next() === $target;
        $isCancel = $target === CatalogOrderStatus::CANCELLED && $current->canCancel();

        if (! $isForward && ! $isCancel) {
            return back()->withErrors(['status' => 'انتقال حالة غير مسموح.']);
        }

        $order->update(['status' => $target]);

        return back()->with('success', 'تم تحديث حالة الطلب');
    }
}
