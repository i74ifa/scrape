<?php

namespace App\Http\Controllers\Api;

use App\Modules\Scraper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);

        $products = Product::query()
            ->with('platform')
            ->when($request->has('platform_id'), function ($query) use ($request) {
                $query->where('platform_id', $request->platform_id);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->cursorPaginate($limit);

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => ['required', 'url'],
            'selectors' => ['required', 'array'],
            'platform_id' => ['required', 'exists:platforms,id'],
        ]);

        $productData = new Scraper($validated['url'], $validated['selectors'], $validated['platform_id']);

        return \App\Models\Product::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return \App\Models\Product::findOrFail($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        \App\Models\Product::destroy($id);

        return response()->noContent();
    }
}
