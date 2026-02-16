<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\Currency;
use App\Services\ScraperDataFactory;
use Illuminate\Foundation\Testing\WithFaker;

class ScraperTest extends TestCase
{
    use WithFaker;

    /**
     * A basic feature test example.
     */
    public function test_can_i_add_to_cart_with_scraper_data(): void
    {
        // $this->seed(PlatformSeeder::class);

        $user = User::factory()->create();
        $this->actingAs($user);

        $platformId = 1;

        $price = $this->faker->randomFloat(2, 10, 500);

        $data = ScraperDataFactory::make(
            platformId: $platformId,
            overrides: [
                'selectors' => [
                    'price' => (string) Currency::format($price, 'SAR'),
                ]
            ],
            currency: 'SAR'
        );

        $response = $this->post(route('carts.store', $platformId), $data);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'platform_id' => $platformId,
            'subtotal' => $price,
        ]);

        $response->assertStatus(200);
    }

    public function test_can_i_add_to_cart_with_tax_and_shipping_price(): void
    {
        // $this->seed(PlatformSeeder::class);

        $user = User::factory()->create();
        $this->actingAs($user);

        $platformId = 1;

        $price = $this->faker->randomFloat(2, 10, 500);

        $data = ScraperDataFactory::make(
            platformId: $platformId,
            overrides: [
                'selectors' => [
                    'price' => (string) Currency::format($price, 'SAR'),
                ]
            ],
            currency: 'SAR'
        );

        $response = $this->post(route('carts.store', $platformId), $data);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'platform_id' => $platformId,
            'subtotal' => $price,
        ]);

        $response->assertStatus(200);
    }
}
