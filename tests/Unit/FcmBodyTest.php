<?php

namespace Tests\Unit;

use App\Services\Fcm\FcmBody;
use Tests\TestCase;

class FcmBodyTest extends TestCase
{
    public function test_body_structure_matches_fcm_v1_example_with_image(): void
    {
        $body = new FcmBody([
            'token' => 'DEVICE_TOKEN',
            'title' => 'New offer!',
            'description' => 'Check this out',
            'image' => 'https://example.com/image.jpg',
        ]);

        $payload = $body->getBody();

        // Token + notification envelope
        $this->assertSame('DEVICE_TOKEN', $payload['message']['token']);
        $this->assertSame('New offer!', $payload['message']['notification']['title']);
        $this->assertSame('Check this out', $payload['message']['notification']['body']);

        // notification.image is populated for cross-platform rich display
        $this->assertSame(
            'https://example.com/image.jpg',
            $payload['message']['notification']['image'],
        );

        // apns.payload.aps.mutable-content = 1 (enables iOS image decoration)
        $this->assertSame(
            1,
            $payload['message']['apns']['payload']['aps']['mutable-content'],
        );

        // apns.fcm_options.image mirrors the rich image for iOS
        $this->assertSame(
            'https://example.com/image.jpg',
            $payload['message']['apns']['fcm_options']['image'],
        );

        // Android notification image mirror
        $this->assertSame(
            'https://example.com/image.jpg',
            $payload['message']['android']['notification']['image'],
        );
    }

    public function test_image_is_omitted_from_all_channels_when_absent(): void
    {
        $body = new FcmBody([
            'token' => 'DEVICE_TOKEN',
            'title' => 'Hello',
            'description' => 'World',
        ]);

        $payload = $body->getBody();

        $this->assertArrayNotHasKey('image', $payload['message']['notification']);
        $this->assertArrayNotHasKey('fcm_options', $payload['message']['apns']);
        $this->assertArrayNotHasKey('image', $payload['message']['android']['notification']);

        // mutable-content defaults to 1 (iOS best-practice for rich pushes),
        // independent of whether an image was supplied.
        $this->assertSame(1, $payload['message']['apns']['payload']['aps']['mutable-content']);
    }

    public function test_mutable_content_can_be_disabled(): void
    {
        $body = new FcmBody([
            'token' => 'DEVICE_TOKEN',
            'title' => 'Hello',
            'description' => 'World',
            'image' => 'https://example.com/image.jpg',
            'mutable_content' => false,
        ]);

        $payload = $body->getBody();

        $this->assertArrayNotHasKey('mutable-content', $payload['message']['apns']['payload']['aps']);
    }
}
