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
                    "page_cols" => 1
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
                    [
                        "url" => "/platforms/5",
                        "cols" => 6,
                        "image" => [
                            "dark" => "https://talabye.com/images/hero-banner-dark.jpg",
                            "light" => "https://talabye.com/images/hero-banner.jpeg"
                        ]
                    ],
                ],
                "title" => "",
                "config" => [
                    "autoplay" => false,
                    "page_cols" => 1
                ]
            ]
        ];

        $sections[] = $bannerSwipe;
        $sections[] = $bannerGrid;

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
                    "page_cols" => 1
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
                    [
                        "url" => "/platforms/5",
                        "cols" => 6,
                        "image" => [
                            "dark" => "https://talabye.com/images/hero-banner-dark.jpg",
                            "light" => "https://talabye.com/images/hero-banner.jpeg"
                        ]
                    ],
                ],
                "title" => "",
                "config" => [
                    "autoplay" => false,
                    "page_cols" => 1
                ]
            ]
        ];

        $sections[] = $bannerSwipe;
        $sections[] = $bannerGrid;

        return response()->json([
            'data' => $sections
        ]);
    }
}
