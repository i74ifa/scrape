<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartBundleResource;
use App\Models\Address;
use App\Models\CartBundle;
use Illuminate\Http\Request;

class CartBundleController extends Controller
{
    public function index(Request $request)
    {
        $cartBundles = CartBundle::where('user_id', user('id'))->get();

        return CartBundleResource::collection($cartBundles);
    }


    public function updateAddress(Address $address)
    {
        $user = user();

        if ($address->user_id !== $user->id) {
            return response()->json([
                'message' => trans('address.not_found'),
            ], 404);
        }

        $cartBundle = CartBundle::getActiveCartBundle();
        $cartBundle->address_id = $address->id;
        $cartBundle->save();

        $cartBundle->updateSummary();

        return response()->json([
            'status' => 'success',
            'message' => trans('address.updated'),
        ]);
    }
}
