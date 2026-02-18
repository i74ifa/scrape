<?php

use Illuminate\Http\Request;
use \App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('addresses', Api\AddressController::class)->middleware('auth:sanctum');
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
    Route::prefix('carts')->name('carts.')->group(function () {
        Route::get('', [Api\CartController::class, 'index'])->name('index');
        Route::post('{platform}', [Api\CartController::class, 'store'])->name('store');
        Route::get('totals', [Api\CartController::class, 'totals']);
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('', [Api\OrderController::class, 'index']);
        Route::get('{order}', [Api\OrderController::class, 'show'])->name('show');

        Route::post('checkout', [Api\OrderController::class, 'checkout']);
    });

    Route::apiResource('payments', Api\PaymentController::class);
});



Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found',
    ], 404);
});
