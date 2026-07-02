<?php

namespace Database\Seeders;

use App\Models\AppSection;
use Illuminate\Database\Seeder;

class AppSectionSeeder extends Seeder
{
    /**
     * Seed the previously-hardcoded home/products layouts so the app keeps
     * working immediately after the move to data-driven sections. Idempotent:
     * a page is only seeded if it has no sections yet.
     */
    public function run(): void
    {
        $layouts = [
            'home' => [
                [
                'name' => 'BannerSwipe',
                    'content' => [
                        'data' => [
                            ['url' => '/platforms/5', 'image' => ['dark' => 'https://talabye.com/images/hero-banner-dark.jpg', 'light' => 'https://talabye.com/images/hero-banner.jpg']],
                            ['url' => '/platforms/2', 'image' => ['dark' => 'https://talabye.com/images/hero-banner-dark.jpg', 'light' => 'https://talabye.com/images/hero-banner.jpg']],
                            ['url' => '/platforms/3', 'image' => ['dark' => 'https://talabye.com/images/hero-banner-dark.jpg', 'light' => 'https://talabye.com/images/hero-banner.jpg']],
                        ],
                        'title' => '',
                        'config' => ['autoplay' => false, 'page_cols' => 1, 'height' => 110],
                    ],
                ],
                [
                    'name' => 'ProductSwipe',
                    'content' => [
                        'data' => [['title' => 'افضل المنتجات', 'url' => '/api/catalog/products?type=best']],
                    ],
                ],
                [
                    'name' => 'ProductGrid',
                    'content' => [
                        'data' => [['title' => 'افضل المنتجات', 'url' => '/api/catalog/products?type=best']],
                    ],
                ],
            ],
            'products' => [
                [
                    'name' => 'CustomBanner',
                    'content' => [
                        'title' => 'تخفيضات شي إن',
                        'description' => 'خصومات تصل إلى 70% على جميع المنتجات',
                        'button' => ['title' => 'تسوق الآن', 'url' => '/platforms/5'],
                        'icon' => ['dark' => 'https://talabye.com/images/icons/shein.png', 'light' => 'https://talabye.com/images/icons/shein.png'],
                        'colors' => ['background' => '#76D2DB', 'text' => '#ffffff', 'button' => '#ffffff', 'button_text' => '#000000'],
                    ],
                ],
                [
                    'name' => 'ProductSwipe',
                    'content' => [
                        'data' => [['title' => 'افضل المنتجات', 'url' => '/api/catalog/products?type=best']],
                    ],
                ],
                [
                    'name' => 'ProductGrid',
                    'content' => [
                        'data' => [['title' => 'افضل المنتجات', 'url' => '/api/catalog/products?type=best']],
                    ],
                ],
            ],
        ];

        foreach ($layouts as $page => $sections) {
            if (AppSection::forPage($page)->exists()) {
                continue;
            }

            foreach ($sections as $index => $section) {
                AppSection::create([
                    'page' => $page,
                    'name' => $section['name'],
                    'content' => $section['content'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        }
    }
}
