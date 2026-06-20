<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreProductRequest;
use App\Http\Requests\Catalog\UpdateProductRequest;
use App\Models\Catalog\Attribute;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand:id,name', 'images']);

        if ($request->filled('search')) {
            $this->applySearch($query, $request->search);
        }

        $products = $query->latest()->paginate(20)->withQueryString();
        $products->getCollection()->transform(function (Product $product) {
            // List thumbnail: the primary image, falling back to the first.
            $primary = $product->images->firstWhere('is_primary', true)
                ?? $product->images->first();
            $product->setAttribute('image', $primary?->url);
            $product->unsetRelation('images');

            return $product;
        });

        return Inertia::render('Admin/Catalog/Products/Index', [
            'products' => $products,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * JSON lookup of catalog products/variants. Each result is a pickable line:
     * a simple product, or one row per variant. Searches name/sku, capped.
     */
    public function lookup(Request $request)
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['variants.attributeValues:id,value']);

        if ($request->filled('search')) {
            $this->applySearch($query, $request->search);
        }

        $results = [];
        foreach ($query->limit(20)->get() as $product) {
            if ($product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    $suffix = $variant->attributeValues->pluck('value')->implode(' / ');
                    $results[] = [
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'name' => $suffix ? "{$product->name} — {$suffix}" : $product->name,
                        'price' => $variant->price ?? $product->price,
                        'sku' => $variant->sku ?? $product->sku,
                    ];
                }
            } else {
                $results[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'name' => $product->name,
                    'price' => $product->price,
                    'sku' => $product->sku,
                ];
            }
        }

        return response()->json($results);
    }

    public function create()
    {
        // Brand is chosen via the async brands.lookup picker — no need to ship
        // the full brand list with the page.
        return Inertia::render('Admin/Catalog/Products/CreateUpdate');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Admin/Catalog/Products/CreateUpdate', [
            'product' => $this->shapeForForm($product),
        ]);
    }

    /**
     * The attributes offered by the product's selected categories — every
     * reusable axis ("اللون", "التخزين") linked to one of `category_ids`, with
     * its accumulated values. Powers the "pick an existing option" helper in the
     * variant editor. With no `category_ids`, returns nothing.
     */
    public function optionSuggestions(Request $request)
    {
        $categoryIds = array_filter(array_map('intval', (array) $request->input('category_ids', [])));

        if (empty($categoryIds)) {
            return response()->json([]);
        }

        return response()->json(
            Attribute::with(['values' => fn ($q) => $q->orderBy('position')])
                ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds))
                ->orderBy('name')
                ->get()
                ->map(fn ($attribute) => [
                    'name' => $attribute->name,
                    'is_color' => $attribute->is_color,
                    'values' => $attribute->values
                        ->map(fn ($v) => ['value' => $v->value, 'color' => $v->color])
                        ->values(),
                ])
        );
    }

    /**
     * Store a single uploaded image and return its disk path + public url. The
     * product form uploads each file here immediately and keeps only the returned
     * path string in its state — so the product save payload stays pure JSON.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('image')->store('products/images', config('filesystems.default'));

        return response()->json([
            'path' => $path,
            'url' => Storage::url($path),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $options = $data['options'] ?? [];
        $variants = $data['variants'] ?? [];
        $images = $data['images'] ?? [];
        unset($data['options'], $data['variants'], $data['images'], $data['category_ids']);

        $product = DB::transaction(function () use ($data, $request, $options, $variants, $images) {
            $product = Product::create([
                ...$data,
                'slug' => $this->uniqueSlug($data['name'], $data['slug'] ?? null),
                'has_variants' => ! empty($variants),
            ]);

            $product->categories()->sync($request->validated('category_ids') ?? []);
            $this->syncImages($product, $images);

            if (! empty($variants)) {
                $this->syncVariants($product, $options, $variants);
            }

            return $product;
        });

        return back()->with('success', 'تمت إضافة المنتج بنجاح');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $options = $data['options'] ?? null;
        $variants = $data['variants'] ?? null;
        // Distinguish "images omitted" (leave gallery untouched) from "images: []"
        // (clear the gallery) — array_key_exists, not null-coalescing.
        $syncImages = array_key_exists('images', $data);
        $images = $data['images'] ?? [];
        unset($data['options'], $data['variants'], $data['images'], $data['category_ids']);

        DB::transaction(function () use ($data, $request, $product, $options, $variants, $syncImages, $images) {
            $product->update([
                ...$data,
                'slug' => $this->uniqueSlug($data['name'], $data['slug'] ?? null, $product->id),
            ]);

            $product->categories()->sync($request->validated('category_ids') ?? []);

            if ($syncImages) {
                $this->syncImages($product, $images);
            }

            // Re-sync variants only when the caller sent a variant payload. A plain
            // field edit leaves the existing variant structure untouched.
            if ($variants !== null) {
                $this->syncVariants($product, $options ?? [], $variants);
            }
        });

        return back()->with('success', 'تم تحديث المنتج بنجاح');
    }

    public function destroy(Product $product)
    {
        $product->delete(); // variants + images + pivots cascade

        return back()->with('success', 'تم حذف المنتج بنجاح');
    }

    /**
     * Apply a name/sku search filter. Plain LIKE (the trigram/Searchable index
     * from the source project is not part of this port).
     */
    private function applySearch($query, string $term): void
    {
        $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    /**
     * Re-sync a product's variants against the store-wide attribute library.
     *
     * Each option is upserted into the library (Attribute::remember —
     * accumulate-only: shared "اللون"/"أحمر" rows are reused, never duplicated).
     * Variants are matched by SKU: matched ones are updated in place; unmatched
     * payload variants are created; existing variants absent from the payload are
     * deleted (pivot cascades). A product's option axes are NOT stored — they are
     * derived from the global values its surviving variants reference.
     *
     * @param  array<int, array{name: string, values: array<int, array{value: string, color?: string|null}>}>  $options
     * @param  array<int, array<string, mixed>>  $variants
     */
    private function syncVariants(Product $product, array $options, array $variants): void
    {
        // Upsert each option into the store library; collect a flat
        // value-string → attribute_value id map. Each option is also linked to
        // the product's categories so the variant editor suggests it for sibling
        // products in those categories.
        $categoryIds = $product->categories()->pluck('categories.id')->all();
        $valueMap = [];
        foreach ($options as $opt) {
            $valueMap += Attribute::remember(
                $opt['name'] ?? '',
                $opt['values'] ?? [],
                $categoryIds,
                array_key_exists('is_color', $opt) ? (bool) $opt['is_color'] : null,
            );
        }

        $keepIds = [];

        foreach ($variants as $v) {
            $attrs = [
                'sku' => $v['sku'] ?? null,
                'barcode' => $v['barcode'] ?? null,
                'price' => $v['price'],
                'sale_price' => $v['sale_price'] ?? null,
                'cost_price' => $v['cost_price'] ?? null,
                'weight' => $v['weight'] ?? null,
                'is_active' => $v['is_active'] ?? true,
            ];

            $existing = ! empty($v['sku'])
                ? $product->variants()->where('sku', $v['sku'])->first()
                : null;

            if ($existing) {
                $existing->update($attrs);
                $variant = $existing;
            } else {
                $variant = $product->variants()->create($attrs);
            }
            $keepIds[] = $variant->id;

            // Link this variant to the shared library values it's built from.
            $ids = collect($v['option_values'])
                ->map(fn ($s) => $valueMap[trim($s)] ?? null)
                ->filter()
                ->values()
                ->all();
            $variant->attributeValues()->sync($ids);
        }

        // Drop variants no longer in the payload (pivot cascades).
        $product->variants()->whereNotIn('id', $keepIds ?: [0])->delete();
        $product->update(['has_variants' => ! empty($variants)]);
    }

    /**
     * Reconcile a product's gallery against the payload of kept image paths (each
     * already uploaded via uploadImage). Kept rows have their position + primary
     * flag refreshed; dropped rows are deleted (file removed); new paths inserted.
     * Exactly one image is primary: the flagged one, or the first otherwise.
     *
     * @param  array<int, array{path: string, is_primary?: bool}>  $images
     */
    private function syncImages(Product $product, array $images): void
    {
        $disk = config('filesystems.default');

        // Normalize + dedupe by path, preserving order; pick the primary.
        $rows = [];
        $primaryPath = null;
        foreach (array_values($images) as $row) {
            $path = $row['path'] ?? '';
            if ($path === '' || isset($rows[$path])) {
                continue;
            }
            if (! empty($row['is_primary']) && $primaryPath === null) {
                $primaryPath = $path;
            }
            $rows[$path] = $path;
        }
        $paths = array_values($rows);
        if ($primaryPath === null && ! empty($paths)) {
            $primaryPath = $paths[0];
        }

        // Delete dropped images (and their files).
        $product->images()
            ->whereNotIn('path', $paths ?: [''])
            ->get()
            ->each(function (ProductImage $image) use ($disk) {
                Storage::disk($disk)->delete($image->path);
                $image->delete();
            });

        // Upsert kept + new in payload order.
        foreach ($paths as $i => $path) {
            $product->images()->updateOrCreate(
                ['path' => $path],
                ['position' => $i, 'is_primary' => $path === $primaryPath],
            );
        }
    }

    /**
     * Build a globally unique slug. Falls back to a sensible base for non-latin
     * (Arabic) names where Str::slug yields an empty string.
     */
    private function uniqueSlug(string $name, ?string $provided, ?int $ignoreId = null): string
    {
        $base = $provided ? Str::slug($provided) : Str::slug($name);
        if ($base === '') {
            $base = 'product';
        }

        $slug = $base;
        $i = 1;
        while (Product::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    /**
     * Flatten a product into the shape the Create/Update form consumes: scalars +
     * category_ids + a clean options/variants structure.
     *
     * The option axes aren't stored on the product — they're DERIVED from the
     * store-wide attribute values the surviving variants reference, grouped back
     * into {name, values[]} so the editor renders the same builder shape it sent.
     *
     * @return array<string, mixed>
     */
    private function shapeForForm(Product $product): array
    {
        $product->load([
            'categories:id,name,path',
            'variants.attributeValues.attribute:id,name,position,is_color',
            'images',
        ]);

        $categoryLabels = Category::pathLabels($product->categories);

        // Rebuild the option axes from the global values the variants reference.
        $options = [];
        foreach ($product->variants as $variant) {
            foreach ($variant->attributeValues as $av) {
                $name = $av->attribute->name;
                $options[$name]['position'] ??= $av->attribute->position;
                $options[$name]['is_color'] ??= $av->attribute->is_color;
                $options[$name]['values'][$av->value] = [
                    'value' => $av->value,
                    'color' => $av->color,
                ];
            }
        }
        $shapedOptions = collect($options)
            ->sortBy('position')
            ->map(fn ($opt, $name) => [
                'name' => $name,
                'is_color' => (bool) ($opt['is_color'] ?? false),
                'values' => array_values($opt['values']),
            ])
            ->values();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'end_discount_date' => optional($product->end_discount_date)->format('Y-m-d'),
            'weight' => $product->weight,
            'sku' => $product->sku,
            'promotion' => $product->promotion,
            'specifications' => $product->specifications ?? [],
            'tags' => $product->tags,
            'brand_id' => $product->brand_id,
            'brand' => $product->brand ? ['id' => $product->brand->id, 'name' => $product->brand->name] : null,
            'is_digital' => $product->is_digital,
            'is_active' => $product->is_active,
            'has_variants' => $product->has_variants,
            'category_ids' => $product->categories->pluck('id'),
            'categories' => $product->categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'path' => $categoryLabels[$c->id] ?? $c->name,
            ]),
            'images' => $product->images->map(fn ($img) => [
                'path' => $img->path,
                'url' => $img->url,
                'is_primary' => $img->is_primary,
            ])->values(),
            'options' => $shapedOptions,
            'variants' => $product->variants->map(function ($v) {
                return [
                    'option_values' => $v->attributeValues->pluck('value')->values(),
                    'sku' => $v->sku,
                    'barcode' => $v->barcode,
                    'price' => $v->price,
                    'sale_price' => $v->sale_price,
                    'cost_price' => $v->cost_price,
                    'weight' => $v->weight,
                    'is_active' => $v->is_active,
                ];
            })->values(),
        ];
    }
}
