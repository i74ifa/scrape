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
        $token = 'cEWyQd7D-kpglGpkEOFzt4:APA91bHacL5w-8jY5XUxNns0oKu5vveOPCEMBJnoZnXGRFJl6s8lw67yf8Zau4CWzpTncRRBg6LW3q9PpGHpZpVMsp4vBPlk1w9vROMMlbj5E8dmkzATm-g';

        $fcm = new Fcm();
        $res = $fcm->send(new FcmBody([
            'token' => $token,
            'title' => 'تحقق من اخر العروض ',
            'description' => 'تحقق من اخر االعروض المخصصه لك فقط💞',
            'url' => '',
        ]));

        return;
    }
}
