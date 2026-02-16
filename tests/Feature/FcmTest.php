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

        
        $fcm = $user->device_token;
        $token = 'c49ZW_nMTU0VhFp9lTa0o2:APA91bH3ALn86bKTq3y1bykpftyzwXw3dotLvLsBMb-UegtYkR0Kf4smwZqVmU1r6aIBQi84XwbN9ksbPxjFhKtPs1EVHDDngMGezmrX3yTRiFSllpXyIGc';

        $fcm = new Fcm();
        $res = $fcm->send(new FcmBody([
            'token' => $token,
            'title' => 'اشعار خاص جدا',
            'description' => 'اشعار خاص ماله فايده ✅',
            'url' => route('dropshippings.queryable',[], false),
        ]));

        return;
    }
}
