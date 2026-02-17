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
        $token = 'fvtssrk-SEiMJq9UHIZtzS:APA91bF-ETHrEAZzAFbYH2Vh90PHOd_C4elGcgnAg3bJ4QutNgZrNlGOxVqhFVy-SRNsoQq49ys9rJ5AwWNKrnG8i8GeSsXR-ieCu8lFFBvyrug28N2vC1M';

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
