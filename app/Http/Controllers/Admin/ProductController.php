<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('platform:id,name,logo,currency_symbol')->latest();

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%");
            });
        }

        if ($request->filled('platform_id')) {
            $query->where('platform_id', $request->integer('platform_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        return Inertia::render('Admin/Product/Index', [
            'products' => $query->paginate(30)->withQueryString(),
            'platforms' => Platform::query()->select('id', 'name', 'logo')->get(),
            'filters' => $request->only(['search', 'status', 'platform_id']),
        ]);
    }
}
