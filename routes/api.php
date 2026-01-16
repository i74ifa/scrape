<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('addresses', Api\AddressController::class);
Route::apiResource('products', Api\ProductController::class)->except(['update']);

Route::group(['prefix' => 'auth'], function () {
    Route::post('/', [Api\AuthController::class, 'sendOtp']);
    Route::post('/verify', [Api\AuthController::class, 'verifyOtp']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('platforms/{platform}/code', [Api\PlatformController::class, 'getCode']);
    Route::apiResource('platforms', Api\PlatformController::class);
});



Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found',
    ], 404);
});
