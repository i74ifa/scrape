<?php

namespace App\Modules;

use Illuminate\Support\Facades\Http;

class M365Dialog
{
    public static function send($to, $text, $countryCode = '967')
    {
        $code = preg_replace('/[^0-9]/', '', $text);
        $to = self::initialPhone($to, $countryCode);
        $key = config('services.365_dialog.api_key');
        $template = config('services.365_dialog.template');
        $url = config('services.365_dialog.url');

        $messageData = [
            'to' => $to,
            'type' => 'template',
            'messaging_product' => 'whatsapp',
            'template' => [
                'namespace' => 'whatsapp:template',
                'name' => $template,
                'language' => ['code' => 'en'],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $code
                            ]
                        ],
                    ],
                    [
                        'type' => 'button',
                        "sub_type" => "url",
                        "index" => "0",
                        'parameters' => [
                            [
                                "type" => "text",
                                "text" => $code,
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return Http::withHeaders([
            'D360-API-KEY' => $key,
            'Content-Type' => 'application/json'
        ])->post($url, $messageData);
    }

    public static function initialPhone($phone, $countryCode = '967')
    {
        if (substr($phone, 0, 1) == '+') {
            return substr($phone, 1);
        }

        // remove zero
        if (substr($phone, 0, 1) == '0') {
            $phone = substr($phone, 1);
        }

        // remove country code
        if (substr($phone, 0, strlen($countryCode)) == $countryCode) {
            $phone = substr($phone, strlen($countryCode));
        }

        return $countryCode . $phone;
    }
}
