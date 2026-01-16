<?php

namespace App\Http\Controllers\Api;

use App\Modules\Scraper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return \App\Models\Product::all();
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

        dd($productData);

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
