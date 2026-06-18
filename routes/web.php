<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CheckoutOrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('auth/telegram', function () {
    return view('login-as-telegram');
});


Route::get('/login-successfully', function (Request $request) {

    if (! $request->hasValidSignature()) {
        abort(401);
    }

    return view('login-successfully');
})->name('login.success');


/*
 * Inertia admin panel, served at /admin.
 * The `inertia.panel` middleware injects shared props and the panel root view.
 */
Route::middleware(['web', 'inertia.panel'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::middleware('guest')->group(function () {
            Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
        });

        Route::middleware('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/products', [ProductController::class, 'index'])->name('products.index');

            Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}/products', [OrderController::class, 'products'])->name('orders.products');
            Route::post('/orders/{order}/next-status', [OrderController::class, 'nextStatus'])->name('orders.next-status');

            Route::get('/checkout-orders', [CheckoutOrderController::class, 'index'])->name('checkout-orders.index');
            Route::get('/checkout-orders/{checkoutOrder}/products', [CheckoutOrderController::class, 'products'])->name('checkout-orders.products');
            Route::post('/checkout-orders/{checkoutOrder}/next-status', [CheckoutOrderController::class, 'nextStatus'])->name('checkout-orders.next-status');
        });
    });

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
