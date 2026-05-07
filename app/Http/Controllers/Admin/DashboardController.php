<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckoutOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'products_count' => Product::count(),
                'orders_count' => Order::where('created_at', '>=', $startOfMonth)->count(),
                'users_count' => User::count(),
                'revenue' => CheckoutOrder::where('status', 'paid')
                    ->where('created_at', '>=', $startOfMonth)
                    ->sum('grand_total'),
            ],
            'activities' => [],
        ]);
    }
}
