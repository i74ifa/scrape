<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\CatalogBrandResource;
use App\Models\Catalog\Brand;
use Illuminate\Http\Request;

/**
 * Public read-only storefront brand browsing. Only active brands are exposed.
 */
class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->withCount('products')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->string('search')}%"))
            ->orderBy('name')
            ->get();

        return CatalogBrandResource::collection($brands);
    }

    public function show(Brand $brand)
    {
        abort_unless($brand->is_active, 404);

        $brand->loadCount('products');

        return new CatalogBrandResource($brand);
    }
}
