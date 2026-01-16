<?php

namespace App\Http\Controllers\Api;

use App\Models\Platform;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlatformController extends Controller
{
    public function index()
    {
        return Platform::all();
    }

    public function getPlatformsCode(Platform $platform)
    {
        $code = $platform->getCode();

        return response($code, 200, [
            'Content-Type' => 'application/javascript',
        ]);
    }
}
