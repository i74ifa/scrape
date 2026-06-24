<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppSection\StoreAppSectionRequest;
use App\Http\Requests\AppSection\UpdateAppSectionRequest;
use App\Models\AppSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AppSectionController extends Controller
{
    /**
     * The simulation page. `page` is a free-form slug (home / products / custom).
     * The page list is the distinct slugs already in the table, plus the currently
     * selected one so a brand-new slug shows up immediately while it is being built.
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 'home');

        $pages = AppSection::query()
            ->select('page')
            ->distinct()
            ->orderBy('page')
            ->pluck('page')
            ->push($page)
            ->unique()
            ->values();

        $sections = AppSection::forPage($page)->ordered()->get([
            'id', 'page', 'name', 'content', 'sort_order', 'is_active',
        ]);

        return Inertia::render('Admin/AppSections/Index', [
            'sections' => $sections,
            'pages' => $pages,
            'filters' => ['page' => $page],
        ]);
    }

    public function store(StoreAppSectionRequest $request)
    {
        $data = $request->validated();

        $nextOrder = (int) AppSection::forPage($data['page'])->max('sort_order') + 1;

        AppSection::create([
            'page' => $data['page'],
            'name' => $data['name'],
            'content' => $data['content'],
            'sort_order' => $nextOrder,
            'is_active' => true,
        ]);

        return back()->with('success', 'تمت إضافة القسم بنجاح');
    }

    public function update(UpdateAppSectionRequest $request, AppSection $appSection)
    {
        $data = $request->validated();

        $appSection->update([
            'name' => $data['name'],
            'content' => $data['content'],
            'is_active' => $data['is_active'] ?? $appSection->is_active,
        ]);

        return back()->with('success', 'تم تحديث القسم بنجاح');
    }

    public function destroy(AppSection $appSection)
    {
        $this->deleteUploadedImages($appSection->content ?? []);

        $appSection->delete();

        return back()->with('success', 'تم حذف القسم بنجاح');
    }

    /**
     * Persist a new ordering for a page. Accepts the full ordered id list and
     * rewrites sort_order by index. Wrapped in a transaction so a partial write
     * can't leave gaps/duplicates.
     */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'page' => ['required', 'string', 'max:120'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['ids'] as $index => $id) {
                AppSection::where('id', $id)
                    ->where('page', $data['page'])
                    ->update(['sort_order' => $index]);
            }
        });

        return back()->with('success', 'تم تحديث ترتيب الأقسام');
    }

    /**
     * Store a single uploaded image and return its disk path + public url. The
     * section editor uploads each file here immediately and embeds only the
     * returned url into the content JSON — so the section save payload stays
     * pure JSON (matches the Catalog\ProductController approach).
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('image')->store('app-sections/images', config('filesystems.default'));

        return response()->json([
            'path' => $path,
            'url' => Storage::url($path),
        ]);
    }

    /**
     * Best-effort removal of any locally-stored images referenced by a section's
     * content. External URLs (http/https that aren't our /storage path) are left
     * alone. Walks the nested array looking for the public storage prefix.
     */
    private function deleteUploadedImages(array $content): void
    {
        $disk = config('filesystems.default');
        $prefix = Storage::url('');

        array_walk_recursive($content, function ($value) use ($disk, $prefix) {
            if (! is_string($value) || $prefix === '' || ! Str::startsWith($value, $prefix)) {
                return;
            }

            $path = ltrim(Str::after($value, $prefix), '/');
            if ($path !== '') {
                Storage::disk($disk)->delete($path);
            }
        });
    }
}
