<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSection;

class AppSectionController extends Controller
{
    /**
     * Resolve the ordered, active sections for a page slug into the
     * `{ data: [{ name, content }, ...] }` shape the Flutter components
     * renderer consumes. Layout is now data-driven (managed from the admin
     * panel) rather than hardcoded.
     */
    protected function page(string $slug)
    {
        $sections = AppSection::forPage($slug)
            ->where('is_active', true)
            ->ordered()
            ->get(['name', 'content'])
            ->map(fn (AppSection $section) => [
                'name' => $section->name,
                'content' => $section->content,
            ]);

        return response()->json([
            'data' => $sections,
        ]);
    }

    public function homePage()
    {
        return $this->page('home');
    }

    public function productsPage()
    {
        return $this->page('products');
    }

    /**
     * Generic resolver for any custom page slug created in the admin panel.
     */
    public function show(string $slug)
    {
        return $this->page($slug);
    }
}
