<?php

namespace App\Services\Fcm;

class FcmBody
{
    public $title;

    public $token;

    public ?string $image = null;

    public ?string $description = null;

    public ?string $url = null;

    public ?string $sound = 'default';

    public ?int $badge = 1;

    public bool $mutableContent = true;

    public function __construct(array $data)
    {
        $this->token = $data['token'];
        $this->title = $data['title'];
        $this->image = $data['image'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->url = $data['url'] ?? null;
        $this->sound = $data['sound'] ?? 'default';
        $this->badge = $data['badge'] ?? 1;
        $this->mutableContent = $data['mutable_content'] ?? true;
    }

    public function getBody(): array
    {
        $notification = [
            'title' => $this->title,
            'body' => $this->description ?? '',
        ];

        // Rich-notification image: FCM v1 accepts `notification.image` for
        // cross-platform rich display. The same URL is also mirrored into
        // `apns.fcm_options.image` (iOS) and `android.notification.image`
        // (Android) so native notification-service extensions can pick it up.
        if ($this->image) {
            $notification['image'] = $this->image;
        }

        $data = [
            'message' => [
                'token' => $this->token,
                'notification' => $notification,
                'data' => [ // must be flat key-value (strings only)
                    'url' => $this->url ?? '',
                    'image' => $this->image ?? '',
                ],
                'android' => [
                    'notification' => array_filter([
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'image' => $this->image,
                    ]),
                ],
                'apns' => array_filter([
                    'payload' => [
                        'aps' => array_filter([
                            'category'         => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound'            => $this->sound,
                            'badge'            => $this->badge,
                            // mutable-content=1 lets the iOS Notification Service
                            // Extension download / decorate the push (e.g. image).
                            'mutable-content' => $this->mutableContent ? 1 : null,
                        ]),
                    ],
                    'fcm_options' => $this->image ? ['image' => $this->image] : null,
                ]),
            ],
        ];

        return $data;
    }
}
