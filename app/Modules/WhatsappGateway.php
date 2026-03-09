<?php

namespace App\Modules;

class WhatsappGateway
{
    public static function sendMessage($to, $message, $countryCode = '967')
    {
        $client = new \GuzzleHttp\Client();
        $client->post(config('services.talabye_whatsapp.url') . '/send/message', [
            'headers' => [
                'X-Device-Id' => config('services.talabye_whatsapp.deviceId'),
            ],
            'auth' => [
                config('services.talabye_whatsapp.username'),
                config('services.talabye_whatsapp.password')
            ],
            'form_params' => [
                'phone' => $countryCode . $to,
                'message' => $message,
            ],
        ]);
    }
}
