<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_can_list_addresses(): void
    {
        Address::create([
            'address_one' => '123 Main St',
            'user_id'     => $this->user->id,
        ]);

        $response = $this->getJson('/api/addresses');

        $response->assertStatus(200)
            ->assertJsonFragment(['address_one' => '123 Main St']);
    }

    public function test_can_create_address(): void
    {
        $data = [
            'address_one' => '456 Elm St',
            'phone'       => '555-1234',
        ];

        $response = $this->postJson('/api/addresses', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('addresses', array_merge($data, ['user_id' => $this->user->id]));
    }

    public function test_can_show_address(): void
    {
        $address = Address::create([
            'address_one' => '789 Oak St',
            'user_id'     => $this->user->id,
        ]);

        $response = $this->getJson("/api/addresses/{$address->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['address_one' => '789 Oak St']);
    }

    public function test_can_update_address(): void
    {
        $address = Address::create([
            'address_one' => 'Old Address',
            'user_id'     => $this->user->id,
        ]);

        $data = ['address_one' => 'New Address'];

        $response = $this->putJson("/api/addresses/{$address->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('addresses', array_merge($data, ['id' => $address->id]));
    }

    public function test_can_delete_address(): void
    {
        $address = Address::create([
            'address_one' => 'To Delete',
            'user_id'     => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/addresses/{$address->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }
}

