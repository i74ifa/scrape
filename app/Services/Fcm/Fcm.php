<?php

namespace App\Services\Fcm;

use Google\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class Fcm
{
    public function send(FcmBody $body)
    {
        $projectId = config('services.fcm.project_id');
        $client = new Client();
        $client->setAuthConfig(storage_path('app/json/app_cert.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();
        $token = $client->getAccessToken();

        $access_token = $token['access_token'];

        $response = Http::withHeaders([
            "Authorization" => "Bearer $access_token",
            'Content-Type' => 'application/json'
        ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $body->getBody());

        $err = $response->failed() ? $response->body() : null;

        if ($err) {
            Log::info($err);
        }
    }
}
