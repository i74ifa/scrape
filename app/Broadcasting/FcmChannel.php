<?php

namespace App\Broadcasting;

use App\Models\User;
use App\Services\Fcm\Fcm;
use Illuminate\Support\Facades\Log;

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

        Log::info('fcmBody', [$fcmBody]);
        if ($fcmBody) {
            $fcm = new Fcm();
            $fcm->send($fcmBody);
        }
    }
}
