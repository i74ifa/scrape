<?php

use Illuminate\Http\Request;
use \App\Http\Controllers\Api;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\BankAccountController;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::delete('/user', [UserController::class, 'destroy'])->middleware('auth:sanctum');
Route::put('/user/update-token', [UserController::class, 'updateDeviceToken'])->middleware('auth:sanctum');

Route::get('tracking/{trackingNumber}', [Api\TrackingOrderController::class, 'index']);

Route::put('addresses/{address}/default', [Api\AddressController::class, 'setDefault']);
Route::apiResource('addresses', Api\AddressController::class)->middleware('auth:sanctum');
Route::put('products/{product}/favorite', [Api\ProductController::class, 'toggleFavorite'])->middleware('auth:sanctum');
Route::apiResource('products', Api\ProductController::class)->except(['update']);

Route::prefix('account-delete')->group(function () {
    Route::post('/send-otp', [Api\AccountDeletionController::class, 'sendOtp']);
    Route::post('/confirm', [Api\AccountDeletionController::class, 'confirm']);
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('/', [Api\AuthController::class, 'sendOtp']);
    Route::post('/verify', [Api\AuthController::class, 'verifyOtp']);
    Route::post('/login', [Api\AuthController::class, 'loginAsPassword']);
    Route::get('/google', [Api\AuthSocialController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [Api\AuthSocialController::class, 'handleGoogleCallback']);

    Route::get('/telegram', [Api\AuthSocialController::class, 'redirectToTelegram']);
    Route::get('/telegram/callback', [Api\AuthSocialController::class, 'handleTelegramCallback']);

    Route::get('/apple', [Api\AuthSocialController::class, 'redirectToApple']);
    Route::match(['get', 'post'], '/apple/callback', [Api\AuthSocialController::class, 'handleAppleCallback']);
});


Route::get('platforms/{platform}/code', [Api\PlatformController::class, 'getCode']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('platforms', Api\PlatformController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('carts')->name('carts.')->group(function () {
        Route::get('', [Api\CartController::class, 'index'])->name('index');
        Route::post('{platform}', [Api\CartController::class, 'store'])->name('store');
        Route::post('product/{product}', [Api\CartController::class, 'storeById'])->name('storeById');
        Route::get('totals', [Api\CartController::class, 'totals']);
        Route::post('{cartItem}/quantity', [Api\CartController::class, 'updateQuantity']);

        Route::put('address/{address}', [Api\CartBundleController::class, 'updateAddress']);

        Route::delete('{cartItem}', [Api\CartController::class, 'destroy']);
        Route::get('count', [Api\CartController::class, 'count']);
    });

    Route::prefix('user')->name('user.')->group(function () {
        Route::post('', [Api\UserController::class, 'update']);
        Route::post('password', [Api\UserController::class, 'updatePassword']);
        Route::get('favorite-products', [Api\UserController::class, 'favorite_products']);
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('', [Api\OrderController::class, 'index']);
        Route::get('{order}', [Api\OrderController::class, 'show'])->name('show');

        Route::post('checkout', [Api\OrderController::class, 'checkout']);
    });

    Route::apiResource('payments', Api\PaymentController::class);

    Route::prefix('notifications')->group(function () {
        Route::get('/', [Api\NotificationController::class, 'index']);
        Route::post('{id}/read', [Api\NotificationController::class, 'markAsRead']);
        Route::post('read-all', [Api\NotificationController::class, 'markAllAsRead']);
        Route::get('count', [Api\NotificationController::class, 'count']);
    });

    Route::prefix('sections')->group(function () {
        Route::get('home', [Api\AppSectionController::class, 'homePage']);
        Route::get('products', [Api\AppSectionController::class, 'productsPage']);
    });

    Route::get('app-summary', function (Request $request) {
        // use cart counts and notifications counts

        $notification = app(Api\NotificationController::class)->count($request);
        $cart = app(Api\CartController::class)->count($request);
        $fav = user()->favorite_products()->count();

        return response()->json([
            'notification' => $notification->original['count'],
            'cart' => $cart->original['count'],
            'favorites' => $fav,
        ]);
    });

    Route::get('currencies', [Api\CurrencyController::class, 'index']);
    Route::post('user/default-currency', [Api\CurrencyController::class, 'update']);
    Route::get('bank-accounts', [BankAccountController::class, 'index']);
});

Route::get('pages/{slug}', [Api\PageController::class, 'show']);


Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found',
    ], 404);
});
