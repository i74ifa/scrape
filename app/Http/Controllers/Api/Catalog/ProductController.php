<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\CatalogProductResource;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Illuminate\Http\Request;

/**
 * Public read-only storefront API for the merchant catalog. Browsing only —
 * catalog writes live in the admin panel.
 */
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->get('limit', 15);

        $products = Product::query()
            ->where('is_active', true)
            ->with(['brand:id,name,slug,image,is_active', 'images', 'variants'])
            ->when($request->filled('brand_id'), fn ($q) => $q->where('brand_id', $request->integer('brand_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search');
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%")
                        ->orWhere('tags', 'like', "%{$term}%");
                });
            })
            // Filter to a category and its whole subtree (one indexed query).
            ->when($request->filled('category_id'), function ($q) use ($request) {
                $category = Category::find($request->integer('category_id'));
                $ids = $category ? $category->subtreeIds() : [$request->integer('category_id')];
                $q->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids));
            })
            ->latest()
            ->cursorPaginate($limit);

        return CatalogProductResource::collection($products);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'brand:id,name,slug,image,is_active',
            'images',
            'categories',
            'variants.attributeValues.attribute:id,name,is_color',
        ]);

        return new CatalogProductResource($product);
    }
}
