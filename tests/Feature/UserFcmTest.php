<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Fcm\Fcm;
use App\Services\Fcm\FcmBody;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserFcmTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_index_lists_users_without_exposing_raw_device_token(): void
    {
        User::factory()->create([
            'name' => 'Customer One',
            'device_token' => 'tok-very-long-secret-string-123',
        ]);
        User::factory()->create([
            'name' => 'Customer Two',
            'device_token' => null,
        ]);

        $response = $this->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('users.data', 3));

        // The raw token must never leak through — only the short mask.
        $response->assertDontSee('tok-very-long-secret-string-123');
        $response->assertSee('tok-very'); // the truncated mask
    }

    public function test_with_device_only_filter_returns_only_token_holders(): void
    {
        User::factory()->create(['device_token' => 'token-A']);
        User::factory()->create(['device_token' => null]);

        $response = $this->get(route('admin.users.index', ['with_device_only' => 1]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.has_device', true));
    }

    public function test_send_fcm_validates_required_fields(): void
    {
        $user = User::factory()->create(['device_token' => 'tok']);

        $response = $this->post(route('admin.users.send-fcm', $user), []);

        $response->assertSessionHasErrors(['title', 'body']);
    }

    public function test_send_fcm_validates_image_must_be_a_valid_image_file(): void
    {
        Storage::fake(config('filesystems.default'));
        $user = User::factory()->create(['device_token' => 'tok']);

        // A text document is not an image and should be rejected.
        $file = UploadedFile::fake()->createWithContent('not-image.txt', 'plain text');

        $response = $this->post(route('admin.users.send-fcm', $user), [
            'title' => 'Hi',
            'body' => 'World',
            'image' => $file,
        ]);

        $response->assertSessionHasErrors(['image']);
    }

    public function test_send_fcm_rejects_user_without_device_token(): void
    {
        $user = User::factory()->create(['device_token' => null]);

        $response = $this->post(route('admin.users.send-fcm', $user), [
            'title' => 'Hi',
            'body' => 'World',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_send_fcm_dispatches_to_fcm_service_with_valid_payload(): void
    {
        Storage::fake(config('filesystems.default'));
        Http::fake(); // safety net: never hit the real FCM endpoint

        $user = User::factory()->create([
            'device_token' => 'device-abc',
            'notification_badges' => 4,
        ]);

        $captured = [];
        $this->app->instance(Fcm::class, new class($captured)
        {
            public function __construct(private array &$captured) {}

            public function send(FcmBody $body): void
            {
                $this->captured[] = $body;
            }
        });

        // Multipart upload — a real fake image file.
        $upload = UploadedFile::fake()->image('offer.jpg', 600, 400);

        $response = $this->post(route('admin.users.send-fcm', $user), [
            'title' => 'عرض جديد!',
            'body' => 'تحقق من العرض',
            'image' => $upload,
            'url' => '/offers',
            'mutable_content' => true,
        ]);

        $response->assertSessionHas('success');
        $this->assertCount(1, $captured);

        // File was stored on the default (publicly-reachable) disk so FCM/APNs
        // can fetch it — not the local `public` disk.
        Storage::disk(config('filesystems.default'))->assertExists('fcm/images/'.$upload->hashName());

        /** @var FcmBody $body */
        $body = $captured[0];
        $payload = $body->getBody();

        $this->assertSame('device-abc', $payload['message']['token']);
        $this->assertSame('عرض جديد!', $payload['message']['notification']['title']);
        $this->assertSame('تحقق من العرض', $payload['message']['notification']['body']);

        // The uploaded image became a public URL mirrored to every channel.
        $this->assertNotEmpty($payload['message']['notification']['image']);
        $this->assertSame(
            $payload['message']['notification']['image'],
            $payload['message']['apns']['fcm_options']['image'],
        );
        $this->assertSame(1, $payload['message']['apns']['payload']['aps']['mutable-content']);
        $this->assertSame('/offers', $payload['message']['data']['url']);
        $this->assertSame(4, $payload['message']['apns']['payload']['aps']['badge']);
    }

    public function test_send_fcm_works_without_an_image(): void
    {
        Http::fake();

        $user = User::factory()->create(['device_token' => 'device-xyz']);

        $captured = [];
        $this->app->instance(Fcm::class, new class($captured)
        {
            public function __construct(private array &$captured) {}

            public function send(FcmBody $body): void
            {
                $this->captured[] = $body;
            }
        });

        $response = $this->post(route('admin.users.send-fcm', $user), [
            'title' => 'مرحبا',
            'body' => 'إشعار نصي فقط',
        ]);

        $response->assertSessionHas('success');
        $this->assertCount(1, $captured);

        $payload = $captured[0]->getBody();
        $this->assertArrayNotHasKey('image', $payload['message']['notification']);
        $this->assertArrayNotHasKey('fcm_options', $payload['message']['apns']);
    }
}
