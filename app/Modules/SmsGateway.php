<?php

namespace App\Modules;

use Illuminate\Support\Facades\Http;

class SmsGateway
{
    /**
     * Send SMS via Sms Gate API.
     *
     * @param string $to
     * @param string $text
     * @return \Illuminate\Http\Client\Response
     */
    public static function send($to, $text, $countryCode = '967')
    {
        $to = self::initialPhone($to, $countryCode);
        $username = config('services.sms_gate.username');
        $password = config('services.sms_gate.password');
        $url = config('services.sms_gate.url');

        $data = [
            'message' => $text,
            'phoneNumbers' => [$to],
        ];

        return Http::withBasicAuth($username, $password)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($url, $data);
    }

    /**
     * Format phone number to include + and country code.
     *
     * @param string $phone
     * @param string $countryCode
     * @return string
     */
    public static function initialPhone($phone, $countryCode = '967')
    {
        if (substr($phone, 0, 1) == '+') {
            return $phone;
        }

        // remove zero
        if (substr($phone, 0, 1) == '0') {
            $phone = substr($phone, 1);
        }

        // remove country code if already there without +
        if (substr($phone, 0, strlen($countryCode)) == $countryCode) {
            $phone = substr($phone, strlen($countryCode));
        }

        return '+' . $countryCode . $phone;
    }
}
