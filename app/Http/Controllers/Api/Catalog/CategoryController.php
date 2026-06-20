<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\CatalogCategoryResource;
use App\Models\Catalog\Category;
use Illuminate\Http\Request;

/**
 * Public read-only storefront category browsing.
 */
class CategoryController extends Controller
{
    /**
     * List categories. By default returns the root level with product counts.
     * - parent_id={id} : direct children of a node (lazy tree expand)
     * - tree=1         : the full nested tree (roots with eager children)
     */
    public function index(Request $request)
    {
        $query = Category::query()
            ->withCount('products')
            ->orderBy('name');

        if ($request->boolean('tree')) {
            // Eager-load the whole nested tree from the roots down.
            $query->whereNull('parent_id')
                ->with('children.children.children.children');
        } elseif ($request->filled('parent_id')) {
            $query->where('parent_id', $request->integer('parent_id'));
        } else {
            $query->whereNull('parent_id');
        }

        return CatalogCategoryResource::collection($query->get());
    }

    public function show(Category $category)
    {
        $category->loadCount('products')->load('children');

        return new CatalogCategoryResource($category);
    }
}
