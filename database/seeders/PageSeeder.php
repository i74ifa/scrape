<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Parsedown;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // from docs/pages

        $pages = [
            [
                'title' => 'الشروط والأحكام',
                'slug' => 'terms-conditions',
                'content' => file_get_contents(base_path('docs/pages/terms-conditions.md')),
            ],
            [
                'title' => 'سياسة الخصوصية',
                'slug' => 'privacy-policy',
                'content' => file_get_contents(base_path('docs/pages/privacy-policy.md')),
            ],
            [
                'title' => 'عن التطبيق',
                'slug' => 'about-us',
                'content' => file_get_contents(base_path('docs/pages/about-us.md')),
            ],
        ];

        $parser = new Parsedown();
        foreach ($pages as $page) {
            if (Page::where('slug', $page['slug'])->exists()) {
                continue;
            }
            $page['content'] = $parser->text($page['content']);

            Page::create($page);
        }
    }
}
