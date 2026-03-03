<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
                'title' => 'Custom Banner',
                'description' => 'Custom Banner',
                'icon' => 'Custom Banner',
                'button' => [
                    'title' => 'Custom Banner',
                    'url' => 'Custom Banner'
                ],
                'colors' => [
                    'background' => '#fff',
                    'text' => '#000',
                    'button' => '#fff',
                    'button_text' => '#000'
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
