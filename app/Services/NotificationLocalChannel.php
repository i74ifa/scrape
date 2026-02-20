<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
// use Illuminate\Notifications\Notification as Notificationable;


class NotificationLocalChannel
{
    /**
     * Send a notification to a user.
     *
     * @param User $user
     * @param string $title
     * @param string $body
     * @param string $type
     * @param string $url
     * @param array $extraData
     * @return void
     */
    public function send($notifiable, $notification): void
    {
        $data = $notification->toLocal($notifiable);

        Notification::create([
            'user_id' => $notifiable->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'],
            'url' => $data['url'],
            'read' => false,
        ]);
    }
}
