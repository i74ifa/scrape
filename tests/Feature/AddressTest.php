<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressTest extends TestCase
{

    use RefreshDatabase;

    public function test_can_list_addresses()
    {
        $address = \App\Models\Address::create(['address_one' => '123 Main St']);
        
        $response = $this->getJson('/api/addresses');

        $response->assertStatus(200)
            ->assertJsonFragment(['address_one' => '123 Main St']);
    }

    public function test_can_create_address()
    {
        $data = [
            'address_one' => '456 Elm St',
            'phone' => '555-1234',
        ];

        $response = $this->postJson('/api/addresses', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('addresses', $data);
    }

    public function test_can_show_address()
    {
        $address = \App\Models\Address::create(['address_one' => '789 Oak St']);

        $response = $this->getJson("/api/addresses/{$address->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['address_one' => '789 Oak St']);
    }

    public function test_can_update_address()
    {
        $address = \App\Models\Address::create(['address_one' => 'Old Address']);

        $data = ['address_one' => 'New Address'];

        $response = $this->putJson("/api/addresses/{$address->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('addresses', $data);
    }

    public function test_can_delete_address()
    {
        $address = \App\Models\Address::create(['address_one' => 'To Delete']);

        $response = $this->deleteJson("/api/addresses/{$address->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }
}
