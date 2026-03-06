<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class AppSectionController extends Controller
{
    public function productsPage()
    {
        $sections = [];

        $bannerSwipe = [
            "name" => "BannerSwipe",
            "content" => [
                "data" => [
                    [
                        "url" => "/platforms/5",
                        "image" => [
                            "dark" => "https://talabye.com/images/hero-banner-dark.jpg",
                            "light" => "https://talabye.com/images/hero-banner.jpg"
                        ]

                    ],
                    [
                        "url" => "/platforms/5",
                        "image" => [
                            "dark" => "https://talabye.com/images/hero-banner-dark.jpg",
                            "light" => "https://talabye.com/images/hero-banner.jpg"
                        ]
                    ],
                    [
                        "url" => "/platforms/1",
                        "image" => [
                            "dark" => "https://talabye.com/images/hero-banner-dark.jpg",
                            "light" => "https://talabye.com/images/hero-banner.jpg"
                        ]
                    ]
                ],
                "title" => "",
                "config" => [
                    "autoplay" => false,
                    "page_cols" => 1,
                    "height" => 110,
                ]
            ]
        ];

        $bannerGrid = [
            "name" => "BannerGrid",
            "content" => [
                "data" => [
                    [
                        "url" => "/platforms/5",
                        "cols" => 6,
                        "image" => [
                            "dark" => "https://talabye.com/images/demo-dark.jpg",
                            "light" => "https://talabye.com/images/demo-light.jpg"
                        ]
                    ],
                    // [
                    //     "url" => "/platforms/5",
                    //     "cols" => 6,
                    //     "image" => [
                    //         "dark" => "https://talabye.com/images/demo-dark.jpg",
                    //         "light" => "https://talabye.com/images/demo-light.jpg"
                    //     ]
                    // ],
                ],
                "title" => "",
                "config" => [
                    "autoplay" => false,
                    "page_cols" => 1,
                ]
            ]
        ];

        $customBanner = [
            'name' => 'CustomBanner',
            'content' => [
                'title' => 'تخفيضات شي إن',
                'description' => 'خصومات تصل إلى 70% على جميع المنتجات',
                'button' => [
                    'title' => 'تسوق الآن',
                    'url' => '/platforms/5'
                ],
                'icon' => [
                    'dark' => 'https://talabye.com/images/icons/shein.png',
                    'light' => 'https://talabye.com/images/icons/shein.png'
                ],
                'colors' => [
                    'background' => '#76D2DB',
                    'text' => '#ffffff',
                    'button' => '#ffffff',
                    'button_text' => '#000000'
                ]
            ]
        ];

        // $sections[] = $bannerSwipe;
        // $sections[] = $bannerGrid;
        $sections[] = $customBanner;

        return response()->json([
            'data' => $sections
        ]);
    }

    public function homePage()
    {
        $sections = [];

        $bannerSwipe = [
            "name" => "BannerSwipe",
            "content" => [
                "data" => [
                    [
                        "url" => "/platforms/5",
                        "image" => [
                            "dark" => "https://talabye.com/images/hero-banner-dark.jpg",
                            "light" => "https://talabye.com/images/hero-banner.jpg"
                        ]

                    ],
                    [
                        "url" => "/platforms/2",
                        "image" => [
                            "dark" => "https://talabye.com/images/hero-banner-dark.jpg",
                            "light" => "https://talabye.com/images/hero-banner.jpg"
                        ]
                    ],
                    [
                        "url" => "/platforms/3",
                        "image" => [
                            "dark" => "https://talabye.com/images/hero-banner-dark.jpg",
                            "light" => "https://talabye.com/images/hero-banner.jpg"
                        ]
                    ]
                ],
                "title" => "",
                "config" => [
                    "autoplay" => false,
                    "page_cols" => 1,
                    "height" => 110,
                ]
            ]
        ];

        $bannerGrid = [
            "name" => "BannerGrid",
            "content" => [
                "data" => [
                    [
                        "url" => "/platforms/2",
                        "cols" => 6,
                        "image" => [
                            "dark" => "https://talabye.com/images/demo-dark.jpg",
                            "light" => "https://talabye.com/images/demo-light.jpg"
                        ]
                    ],
                    // [
                    //     "url" => "/platforms/3",
                    //     "cols" => 6,
                    //     "image" => [
                    //         "dark" => "https://talabye.com/images/demo-dark.jpg",
                    //         "light" => "https://talabye.com/images/demo-light.jpg"
                    //     ]
                    // ],
                ],
                "title" => "",
                "config" => [
                    "autoplay" => false,
                    "page_cols" => 1
                ]
            ]
        ];

        $sections[] = $bannerSwipe;
        // $sections[] = $bannerGrid;

        return response()->json([
            'data' => $sections
        ]);
    }
}
