<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Platform;
use App\Modules\Scraper;
use App\Services\Weight;
use App\Services\Currency;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PlatformResource;
use Illuminate\Support\Facades\Validator;

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
