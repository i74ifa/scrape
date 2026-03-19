<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartBundleResource;
use App\Models\CartBundle;
use Illuminate\Http\Request;

class CartBundleController extends Controller
{
    public function index(Request $request)
    {
        $cartBundles = CartBundle::where('user_id', user('id'))->get();

        return CartBundleResource::collection($cartBundles);
    }
}
