<?php

namespace Tests\Feature;

use App\Dropshipping\Enums\DropshippingPaymentStatus;
use App\Models\DropshippingOrder;
use App\Notifications\DropshippingOrderStatusNotify;
use App\Services\Fcm\Fcm;
use App\Services\Fcm\FcmBody;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FcmTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_send_to_specific_device(): void
    {

        $user = \App\Models\User::find(71);


        // $user->notify(
        //     new DropshippingOrderStatusNotify(
        //         DropshippingOrder::find(1))
        //     );
        //     return;


        // $fcm = $user->device_token;
        $token = 'fjbot7REPEYFiv_5hYVRQD:APA91bFaW5oMGr7S4kri3HYFrpvDuCWM97mzED06dRoPUvXMHiF-h-p1Xq7wogTDiEOaWMWVd1R55CIyy_-F3GTJkcftuhBD7ALx0CnLj7Bv5NeflQY-AqU';

        $fcm = new Fcm();
        $res = $fcm->send(new FcmBody([
            'token' => $token,
            'title' => 'اشعار خاص جدا',
            'description' => 'اشعار خاص ماله فايده ✅',
            'url' => '',
        ]));

        return;
    }
}
