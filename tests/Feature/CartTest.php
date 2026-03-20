<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Platform;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartBundle;
use App\Services\Currency;
use App\Services\ScraperDataFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CartTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Platform $platform;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->platform = Platform::create([
            'name'            => 'Test Platform',
            'url'             => 'http://testplatform.com',
            'logo'            => 'logo.png',
            'currency'        => 'SAR',
            'currency_symbol' => 'SAR',
            'country'         => 'SA',
            'script_file'     => 'script.js',
        ]);

        $this->actingAs($this->user);
    }

    // ──────────────────────────────────────────────
    // Helper: create a product belonging to the platform
    // ──────────────────────────────────────────────
    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name'        => 'Test Product',
            'image'       => 'image.jpg',
            'price'       => 100.00,
            'sale_price'  => 100.00,
            'url'         => 'https://www.amazon.com/test-product',
            'weight'      => 500,
            'platform_id' => $this->platform->id,
            'user_id'     => $this->user->id,
            'slug'        => 'test-product',
        ], $overrides));
    }

    // ──────────────────────────────────────────────
    // 1. Add to cart via product_id (store endpoint)
    // ──────────────────────────────────────────────

    public function test_can_add_to_cart_with_product_id(): void
    {
        $product = $this->makeProduct(['price' => 200.00, 'sale_price' => 200.00]);

        $response = $this->postJson(route('carts.store', $this->platform->id), [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => $product->name]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 1,
            'price'      => 200.00,
            'total'      => 200.00,
        ]);
    }

    // ──────────────────────────────────────────────
    // 2. Add to cart via URL + selectors (from selectors-test.json)
    // ──────────────────────────────────────────────

    public function test_can_add_to_cart_with_url_and_selectors(): void
    {
        $data = ScraperDataFactory::make(platformId: $this->platform->id, currency: 'SAR');

        $response = $this->postJson(route('carts.store', $this->platform->id), $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('carts', [
            'user_id'     => $this->user->id,
            'platform_id' => $this->platform->id,
        ]);

        // A product should have been scraped/created from the factory URL
        $this->assertDatabaseHas('products', [
            'url'         => $data['url'],
            'platform_id' => $this->platform->id,
        ]);
    }

    // ──────────────────────────────────────────────
    // 3. Validation: store requires either product_id or selectors
    // ──────────────────────────────────────────────

    public function test_store_fails_without_product_id_or_selectors(): void
    {
        $response = $this->postJson(route('carts.store', $this->platform->id), [
            'url' => 'https://www.amazon.com/some-product',
            // no selectors, no product_id
        ]);

        $response->assertStatus(400);
    }

    // ──────────────────────────────────────────────
    // 4. Amount / totals correctness (subtotal, tax, total)
    // ──────────────────────────────────────────────

    public function test_cart_amounts_are_calculated_correctly_after_add(): void
    {
        $price   = 200.00;
        $product = $this->makeProduct(['price' => $price, 'sale_price' => $price]);

        $this->postJson(route('carts.store', $this->platform->id), [
            'product_id' => $product->id,
        ]);

        $cart = Cart::where('user_id', $this->user->id)
            ->where('platform_id', $this->platform->id)
            ->firstOrFail();

        $expectedSubtotal = $price;              // 1 item × 200
        $expectedTax      = $expectedSubtotal * 0.05; // 5% tax
        $expectedTotal    = $expectedSubtotal + $expectedTax;

        $this->assertEquals($expectedSubtotal, (float) $cart->subtotal, 'Subtotal mismatch', 0.01);
        $this->assertEquals($expectedTax,      (float) $cart->tax,      'Tax mismatch',      0.01);
        $this->assertEquals($expectedTotal,    (float) $cart->total,    'Total mismatch',    0.01);
    }

    public function test_cart_totals_are_correct_with_multiple_quantities(): void
    {
        $price   = 150.00;
        $product = $this->makeProduct(['price' => $price, 'sale_price' => $price]);

        // Add the same product twice to increment quantity
        $this->postJson(route('carts.store', $this->platform->id), [
            'product_id' => $product->id,
        ]);
        $this->postJson(route('carts.store', $this->platform->id), [
            'product_id' => $product->id,
        ]);

        $cart = Cart::where('user_id', $this->user->id)
            ->where('platform_id', $this->platform->id)
            ->firstOrFail();

        $expectedSubtotal = $price * 2;
        $expectedTax      = $expectedSubtotal * 0.05;
        $expectedTotal    = $expectedSubtotal + $expectedTax;

        $this->assertEquals(2, $cart->items()->first()->quantity, 'Quantity should be 2');
        $this->assertEquals($expectedSubtotal, (float) $cart->subtotal, 'Subtotal mismatch', 0.01);
        $this->assertEquals($expectedTax,      (float) $cart->tax,      'Tax mismatch',      0.01);
        $this->assertEquals($expectedTotal,    (float) $cart->total,    'Total mismatch',    0.01);
    }

    // ──────────────────────────────────────────────
    // 5. Duplicate product increments quantity (not duplicate row)
    // ──────────────────────────────────────────────

    public function test_adding_same_product_twice_increments_quantity(): void
    {
        $product = $this->makeProduct();

        $this->postJson(route('carts.store', $this->platform->id), [
            'product_id' => $product->id,
        ]);
        $this->postJson(route('carts.store', $this->platform->id), [
            'product_id' => $product->id,
        ]);

        $itemCount = CartItem::where('product_id', $product->id)->count();
        $this->assertEquals(1, $itemCount, 'Should only have one cart_item row');

        $item = CartItem::where('product_id', $product->id)->first();
        $this->assertEquals(2, $item->quantity);
    }

    // ──────────────────────────────────────────────
    // 6. storeById: adds with explicit quantity
    // ──────────────────────────────────────────────

    public function test_can_add_to_cart_by_product_id_with_quantity(): void
    {
        $price   = 50.00;
        $product = $this->makeProduct(['price' => $price, 'sale_price' => $price]);

        $response = $this->postJson(route('carts.storeById', $product->id), [
            'quantity' => 3,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 3,
            'price'      => $price,
            'total'      => $price * 3,
        ]);
    }

    public function test_store_by_id_fails_without_quantity(): void
    {
        $product = $this->makeProduct();

        $response = $this->postJson(route('carts.storeById', $product->id), []);

        $response->assertStatus(400);
    }

    // ──────────────────────────────────────────────
    // 7. updateQuantity: correct amounts after update
    // ──────────────────────────────────────────────

    public function test_can_update_cart_item_quantity(): void
    {
        $price   = 80.00;
        $product = $this->makeProduct(['price' => $price, 'sale_price' => $price]);

        $this->postJson(route('carts.storeById', $product->id), ['quantity' => 1]);

        $cartItem = CartItem::where('product_id', $product->id)->firstOrFail();

        $response = $this->postJson("/api/carts/{$cartItem->id}/quantity", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);

        $this->assertDatabaseHas('cart_items', [
            'id'       => $cartItem->id,
            'quantity' => 5,
            'total'    => $price * 5,
        ]);

        $cart = $cartItem->fresh()->cart;
        $this->assertEquals($price * 5, (float) $cart->subtotal, 'Subtotal mismatch', 0.01);
        $this->assertEquals($price * 5 * 0.05, (float) $cart->tax, 'Tax mismatch', 0.01);
    }

    public function test_setting_quantity_to_zero_deletes_cart_item(): void
    {
        $product = $this->makeProduct();

        $this->postJson(route('carts.storeById', $product->id), ['quantity' => 2]);

        $cartItem = CartItem::where('product_id', $product->id)->firstOrFail();

        $response = $this->postJson("/api/carts/{$cartItem->id}/quantity", [
            'quantity' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    // ──────────────────────────────────────────────
    // 8. destroy: removes cart item
    // ──────────────────────────────────────────────

    public function test_can_delete_cart_item(): void
    {
        $product = $this->makeProduct();

        $this->postJson(route('carts.storeById', $product->id), ['quantity' => 1]);

        $cartItem = CartItem::where('product_id', $product->id)->firstOrFail();

        $response = $this->deleteJson("/api/carts/{$cartItem->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    // ──────────────────────────────────────────────
    // 9. totals endpoint returns correctly formatted values
    // ──────────────────────────────────────────────

    public function test_totals_returns_zero_when_no_cart_bundle(): void
    {
        $response = $this->getJson('/api/carts/totals');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'subtotal' => 0,
                'tax'      => 0,
                'total'    => 0,
            ]);
    }

    public function test_totals_are_correct_after_adding_product(): void
    {
        $price   = 100.00;
        $product = $this->makeProduct(['price' => $price, 'sale_price' => $price]);

        $this->postJson(route('carts.storeById', $product->id), ['quantity' => 2]);

        $response = $this->getJson('/api/carts/totals');
        $response->assertStatus(200);

        // Verify the underlying CartBundle has the correct subtotal in DB
        $bundle = CartBundle::where('user_id', $this->user->id)->firstOrFail();

        $expectedSubtotal = $price * 2;          // 200.00
        $expectedTax      = $expectedSubtotal * 0.05; // 10.00
        $expectedTotal    = $expectedSubtotal + $expectedTax; // 210.00

        $this->assertEquals($expectedSubtotal, (float) $bundle->subtotal, 'Bundle subtotal mismatch', 0.01);
        $this->assertEquals($expectedTax,      (float) $bundle->tax,      'Bundle tax mismatch',      0.01);
        $this->assertEquals($expectedTotal,    (float) $bundle->total,    'Bundle total mismatch',    0.01);
    }
}
