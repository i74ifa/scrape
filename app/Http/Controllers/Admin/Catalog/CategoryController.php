<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreCategoryRequest;
use App\Http\Requests\Catalog\UpdateCategoryRequest;
use App\Models\Catalog\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // Table view: server-paginated + async search. Immediate parent name is
        // eager-loaded; child/product counts are subquery columns (no N+1). The
        // tree view and parent picker load lazily via lookup() instead.
        $query = Category::query()
            ->select('id', 'name', 'slug', 'parent_id', 'image')
            ->with('parent:id,name')
            ->withCount('children')
            ->withCount('products');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query->orderBy('name')->paginate(20)->withQueryString();

        // Expose the public image url so the table/edit form can show the
        // current art without leaking the raw disk path.
        $categories->getCollection()->each(
            fn (Category $c) => $c->setAttribute('image', $c->imageUrl())
        );

        return Inertia::render('Admin/Catalog/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * JSON lookup feeding two async UIs: the create/edit parent picker (search)
     * and the lazy tree (children). Always capped at 30 rows.
     */
    public function lookup(Request $request)
    {
        $query = Category::query()
            ->select('id', 'name', 'parent_id', 'path', 'image')
            ->withCount('children')
            ->withCount('products')
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        } elseif ($request->filled('parent_id')) {
            $query->where('parent_id', $request->integer('parent_id'));
        } else {
            $query->whereNull('parent_id');
        }

        if ($request->filled('exclude')) {
            $query->whereKeyNot($request->integer('exclude'));
        }

        $categories = $query->limit(30)->get();
        $labels = Category::pathLabels($categories);

        return response()->json(
            $categories->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'path' => $labels[$c->id] ?? $c->name,
                'parent_id' => $c->parent_id,
                'image' => $c->imageUrl(),
                'has_children' => $c->children_count > 0,
                'products_count' => $c->products_count,
            ])
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        Category::create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'name_translations' => $data['name_translations'] ?? null,
            'slug' => $this->uniqueSlug($data['name'], $data['slug'] ?? null),
            'image' => $request->hasFile('image')
                ? $request->file('image')->store('categories/images', config('filesystems.default'))
                : null,
        ]);

        return back()->with('success', 'تمت إضافة التصنيف بنجاح');
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        $category->update([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'name_translations' => $data['name_translations'] ?? null,
            'slug' => $this->uniqueSlug($data['name'], $data['slug'] ?? null, $category->id),
            'image' => $this->resolveImage($request, $category->image),
        ]);

        return back()->with('success', 'تم تحديث التصنيف بنجاح');
    }

    public function destroy(Category $category)
    {
        // Detach products from this category before deleting (the relation is a
        // pivot, so this just removes the link rows — products are untouched).
        $category->products()->detach();

        $category->delete();

        return back()->with('success', 'تم حذف التصنيف بنجاح');
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

            return $request->file('image')->store('categories/images', $disk);
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
     * Build a globally unique slug. Falls back to a numeric suffix for non-latin
     * (e.g. Arabic) names where Str::slug is empty.
     */
    private function uniqueSlug(string $name, ?string $provided, ?int $ignoreId = null): string
    {
        $base = $provided ? Str::slug($provided) : Str::slug($name);
        if ($base === '') {
            $base = 'category';
        }

        $slug = $base;
        $i = 1;
        while (Category::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
