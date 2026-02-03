<?php

use Illuminate\Http\Request;
use \App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('addresses', Api\AddressController::class);
Route::apiResource('products', Api\ProductController::class)->except(['update']);

Route::group(['prefix' => 'auth'], function () {
    Route::post('/', [Api\AuthController::class, 'sendOtp']);
    Route::post('/verify', [Api\AuthController::class, 'verifyOtp']);
});


Route::get('platforms/{platform}/code', [Api\PlatformController::class, 'getCode']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('platforms', Api\PlatformController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('carts', [Api\CartController::class, 'index']);
    Route::post('carts/{platform}', [Api\CartController::class, 'store']);
});

Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::get('', [Api\OrderController::class, 'index']);
    Route::post('checkout', [Api\OrderController::class, 'checkout']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('payments', Api\PaymentController::class);
});



Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found',
    ], 404);
});
