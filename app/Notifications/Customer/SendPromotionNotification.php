<?php

namespace App\Notifications\Customer;

use App\Enums\NotificationType;
use App\Services\Fcm\FcmBody;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SendPromotionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $title,
        protected string $body,
        protected ?string $url = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['fcm', 'local'];
    }

    public function toFcm($notifiable): FcmBody
    {
        return new FcmBody([
            'title'       => $this->title,
            'description' => $this->body,
            'url'         => $this->url ?? '',
            'token'       => $notifiable->device_token,
        ]);
    }

    public function toLocal($notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'type'  => NotificationType::PROMO->value,
            'url'   => $this->url ?? '',
        ];
    }
}
