<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_service_creates_notification()
    {
        $user = User::factory()->create();
        $service = new NotificationService();

        $notification = $service->send($user, 'Test Title', 'Test Body', 'info');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
            'title' => 'Test Title',
            'body' => 'Test Body',
            'type' => 'info',
            'read' => false,
        ]);
    }

    public function test_can_list_notifications_via_api()
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_mark_notification_as_read_via_api()
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id, 'read' => false]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $this->assertTrue($notification->fresh()->read);
    }

    public function test_can_mark_all_notifications_as_read_via_api()
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->create(['user_id' => $user->id, 'read' => false]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/notifications/read-all");

        $response->assertStatus(200);
        $this->assertEquals(0, $user->notifications()->unread()->count());
    }
}
