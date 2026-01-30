<?php

namespace App\Http\Controllers\Api;

use App\Models\Platform;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PlatformResource;

class PlatformController extends Controller
{
    public function index()
    {
        return PlatformResource::collection(Platform::all());
    }

    public function getCode(Platform $platform)
    {
        $code = $platform->getCode();

        return response($code, 200, [
            'Content-Type' => 'application/javascript',
        ]);
    }
}
