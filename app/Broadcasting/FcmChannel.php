<?php

namespace App\Broadcasting;

use App\Models\User;
use App\Services\Fcm\Fcm;

class FcmChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function send($notifiable, $notification)
    {
        $fcmBody = $notification->toFcm($notifiable);

        if ($fcmBody) {
            $fcm = new Fcm();
            $fcm->send($fcmBody);
        }
    }
}
