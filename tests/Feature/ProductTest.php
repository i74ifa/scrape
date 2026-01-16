<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Platform;
use App\Models\Product;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $platform;

    protected function setUp(): void
    {
        parent::setUp();
        // Create user and platform for tests
        $this->user = User::factory()->create();
        $this->platform = Platform::create([
            'name' => 'Test Platform',
            'url' => 'http://testplatform.com',
            'logo' => 'logo.png',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'country' => 'US',
            'script_file' => 'script.js'
        ]);
    }

    public function test_can_list_products()
    {
        Product::create([
            'name' => 'Product 1',
            'image' => 'image.jpg',
            'price' => 100.00,
            'url' => 'http://example.com',
            'weight' => 500,
            'platform_id' => $this->platform->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Product 1']);
    }

    public function test_can_create_product()
    {
        $data = [
            'name' => 'New Product',
            'image' => 'new.jpg',
            'price' => 150.00,
            'url' => 'http://example.com/new',
            'weight' => 300,
            'platform_id' => $this->platform->id,
            'user_id' => $this->user->id,
            'variants' => ['color' => 'red'],
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'New Product']);

        $this->assertDatabaseHas('products', ['name' => 'New Product']);
    }

    public function test_can_show_product()
    {
        $product = Product::create([
            'name' => 'Show Product',
            'image' => 'show.jpg',
            'price' => 200.00,
            'url' => 'http://example.com/show',
            'weight' => 200,
            'platform_id' => $this->platform->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Show Product']);
    }

    public function test_can_delete_product()
    {
        $product = Product::create([
            'name' => 'Delete Product',
            'image' => 'delete.jpg',
            'price' => 50.00,
            'url' => 'http://example.com/delete',
            'weight' => 100,
            'platform_id' => $this->platform->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
