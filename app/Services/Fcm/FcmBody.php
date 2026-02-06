<?php


namespace App\Services\Fcm;

class FcmBody
{
    public $title;
    public $token;
    public string|null $image = null;
    public string|null $description = null;
    public string|null $url = null;


    public function __construct(array $data)
    {
        $this->token = $data['token'];
        $this->title = $data['title'];
        $this->image = $data['image'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->url = $data['url'] ?? null;
    }

    public function getBody(): array
    {
        $data = [
            'message' => [
                'token' => $this->token,
                'notification' => [
                    'title' => $this->title,
                    'body'  => $this->description ?? '',
                ],
                'data' => [ // must be flat key-value (strings only)
                    'url'   => $this->url ?? '',
                    'image' => $this->image ?? '',
                ],
                'android' => [
                    'notification' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'category' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                ],
            ]
        ];
        // if ($this->description) {
        //     $data['message']['notification']['body'] = $this->description;
        // }

        // if ($this->image) {
        //     $data['message']['android']['notification']['imageUrl'] = $this->image;
        // }
        return $data;
    }
}
