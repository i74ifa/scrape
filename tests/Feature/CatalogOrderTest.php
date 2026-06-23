<?php

namespace Tests\Feature;

use App\Enums\CatalogOrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Catalog\CatalogOrder;
use App\Models\User;
use App\Services\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Other tests in the suite mutate the static active currency and leave
        // it null, which breaks money(). The admin show route renders amounts
        // via money(), so reset it to a known value before each test here.
        Currency::$currency = 'YER';

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    private function makeOrder(array $overrides = []): CatalogOrder
    {
        return CatalogOrder::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'code' => CatalogOrder::generateCode(),
            'status' => CatalogOrderStatus::PENDING_PAYMENT,
            'subtotal' => 100.00,
            'total' => 100.00,
            'total_quantity' => 1,
            'payment_method' => PaymentMethod::BANKS_TRANSFER,
            'payment_reference' => [
                'bank_name' => 'Al Rajhi',
                'bank_id' => 'TXN-123',
                'iban' => 'SA00 0000',
                'image' => 'images/receipt.jpg',
            ],
        ], $overrides));
    }

    public function test_show_exposes_payment_reference_with_resolved_image_url(): void
    {
        Storage::fake('public');

        $order = $this->makeOrder();
        $expectedUrl = Storage::url('images/receipt.jpg');

        $response = $this->get(route('admin.catalog.orders.show', $order));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->where('order.payment_reference.image', 'images/receipt.jpg')
                ->where('order.payment_reference.image_url', $expectedUrl)
                ->where('order.payment_reference.bank_name', 'Al Rajhi')
                ->where('order.is_pending_payment', true)
        );
    }

    public function test_confirm_payment_advances_pending_payment_to_pending(): void
    {
        $order = $this->makeOrder();

        $response = $this->post(
            route('admin.catalog.orders.status', $order),
            ['status' => CatalogOrderStatus::PENDING->value],
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame(
            CatalogOrderStatus::PENDING,
            $order->fresh()->status,
        );
    }

    public function test_reject_payment_cancels_the_order(): void
    {
        $order = $this->makeOrder();

        $response = $this->post(
            route('admin.catalog.orders.status', $order),
            ['status' => CatalogOrderStatus::CANCELLED->value],
        );

        $response->assertRedirect();

        $this->assertSame(
            CatalogOrderStatus::CANCELLED,
            $order->fresh()->status,
        );
    }

    public function test_illegal_status_transition_is_rejected(): void
    {
        $order = $this->makeOrder(); // pending_payment

        // PENDING_PAYMENT -> DELIVERED is not a legal single forward step nor a cancel.
        $response = $this->post(
            route('admin.catalog.orders.status', $order),
            ['status' => CatalogOrderStatus::DELIVERED->value],
        );

        $response->assertSessionHasErrors('status');
        $this->assertSame(
            CatalogOrderStatus::PENDING_PAYMENT,
            $order->fresh()->status,
        );
    }
}
