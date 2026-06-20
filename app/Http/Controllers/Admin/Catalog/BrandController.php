<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreBrandRequest;
use App\Http\Requests\Catalog\UpdateBrandRequest;
use App\Models\Catalog\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query()->withCount('products');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $brands = $query->latest()->paginate(20)->withQueryString();

        $brands->getCollection()->each(
            fn (Brand $b) => $b->setAttribute('image', $b->imageUrl())
        );

        return Inertia::render('Admin/Catalog/Brands/Index', [
            'brands' => $brands,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    /**
     * JSON lookup feeding the async brand picker in the product form. Searches
     * by name, caps at 30 rows; an empty query returns the most recent brands.
     */
    public function lookup(Request $request)
    {
        $query = Brand::query()->select('id', 'name');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return response()->json(
            $query->orderBy('name')->limit(30)->get()
        );
    }

    public function store(StoreBrandRequest $request)
    {
        $data = $request->validated();

        Brand::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name'], $data['slug'] ?? null),
            'image' => $request->hasFile('image')
                ? $request->file('image')->store('brands/images', config('filesystems.default'))
                : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'تمت إضافة العلامة التجارية بنجاح');
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $data = $request->validated();

        $brand->update([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name'], $data['slug'] ?? null, $brand->id),
            'image' => $this->resolveImage($request, $brand->image),
            'is_active' => $request->boolean('is_active', $brand->is_active),
        ]);

        return back()->with('success', 'تم تحديث العلامة التجارية بنجاح');
    }

    public function destroy(Brand $brand)
    {
        // Detach products from the brand before deleting so they survive as
        // brand-less rows (matches the nullable nullOnDelete FK). The frontend
        // warns when products_count > 0 before this runs.
        $brand->products()->update(['brand_id' => null]);

        $brand->delete();

        return back()->with('success', 'تم حذف العلامة التجارية بنجاح');
    }

    /**
     * Resolve the image column on update. A newly uploaded file replaces the old
     * one (and deletes it); `remove_image` clears it; otherwise the current path
     * is kept untouched.
     */
    private function resolveImage(Request $request, ?string $current): ?string
    {
        $disk = config('filesystems.default');

        if ($request->hasFile('image')) {
            if ($current && ! Str::startsWith($current, ['http://', 'https://'])) {
                Storage::disk($disk)->delete($current);
            }

            return $request->file('image')->store('brands/images', $disk);
        }

        if ($request->boolean('remove_image')) {
            if ($current && ! Str::startsWith($current, ['http://', 'https://'])) {
                Storage::disk($disk)->delete($current);
            }

            return null;
        }

        return $current;
    }

    /**
     * Build a globally unique slug. Falls back to a sensible base for non-latin
     * (e.g. Arabic) names where Str::slug is empty.
     */
    private function uniqueSlug(string $name, ?string $provided, ?int $ignoreId = null): string
    {
        $base = $provided ? Str::slug($provided) : Str::slug($name);
        if ($base === '') {
            $base = 'brand';
        }

        $slug = $base;
        $i = 1;
        while (Brand::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
