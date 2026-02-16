<?php

namespace App\Services;

class ScraperDataFactory
{
    /**
     * Generate mock scraper data.
     *
     * @param int $platformId
     * @param array $overrides
     * @return array
     */
    public static function make(int $platformId, array $overrides = [], $currency = 'USD'): array
    {
        $faker = \Faker\Factory::create('en_US');
        $price = $faker->randomFloat(2, 10, 500);
        $imageUrl = $faker->imageUrl(640, 480, 'products');

        $defaultSelectors = [
            'name' => $faker->words(10, true),
            'description' => $faker->paragraph(),
            'price' => (string) Currency::format($price, $currency),
            'originalPrice' => (string) Currency::format($price, $currency),
            'image' => $imageUrl,
            'images' => [],
            'review' => (string) $faker->numberBetween(0, 100),
            'category' => $faker->word(),
            'brand' => $faker->company(),
            'averageRating' => (string) $faker->randomFloat(1, 1, 5),
            'totalReviews' => (string) $faker->numberBetween(1, 1000),
            'soldBy' => $faker->company(),
            'shippingPrice' => (string) $faker->randomFloat(2, 0, 50),
            'selectedVariant' => $faker->word(),
            'id' => (string) $faker->uuid(),
        ];


        $selectorsData = array_merge($defaultSelectors, $overrides['selectors'] ?? []);
        unset($overrides['selectors']);

        $selectors = [];
        foreach ($selectorsData as $name => $data) {
            $selectors[] = [
                'name' => $name,
                'data' => $data,
            ];
        }

        return [
            'url' => $overrides['url'] ?? 'https://www.amazon.com/Apple-2025-MacBook-13-inch-Laptop/dp/B0DZD9S5GC/',
            'platform_id' => $platformId,
            'selectors' => $selectors,
        ];
    }
}
