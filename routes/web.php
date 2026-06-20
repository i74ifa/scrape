<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\Catalog\AttributeController as CatalogAttributeController;
use App\Http\Controllers\Admin\Catalog\BrandController as CatalogBrandController;
use App\Http\Controllers\Admin\Catalog\CategoryController as CatalogCategoryController;
use App\Http\Controllers\Admin\Catalog\ProductController as CatalogProductController;
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
            Route::get('/checkout-orders/{checkoutOrder}', [CheckoutOrderController::class, 'show'])->name('checkout-orders.show');
            Route::get('/checkout-orders/{checkoutOrder}/products', [CheckoutOrderController::class, 'products'])->name('checkout-orders.products');
            Route::post('/checkout-orders/{checkoutOrder}/next-status', [CheckoutOrderController::class, 'nextStatus'])->name('checkout-orders.next-status');

            /*
             * Catalog: merchant-authored products (categories, brands,
             * attributes, variants) — distinct from the scraped `products`
             * powering cart/orders. Served under /admin/catalog.
             */
            Route::prefix('catalog')->name('catalog.')->group(function () {
                // Categories
                Route::get('categories', [CatalogCategoryController::class, 'index'])->name('categories.index');
                Route::get('categories/lookup', [CatalogCategoryController::class, 'lookup'])->name('categories.lookup');
                Route::post('categories', [CatalogCategoryController::class, 'store'])->name('categories.store');
                Route::put('categories/{category}', [CatalogCategoryController::class, 'update'])->name('categories.update');
                Route::delete('categories/{category}', [CatalogCategoryController::class, 'destroy'])->name('categories.destroy');

                // Brands
                Route::get('brands', [CatalogBrandController::class, 'index'])->name('brands.index');
                Route::get('brands/lookup', [CatalogBrandController::class, 'lookup'])->name('brands.lookup');
                Route::post('brands', [CatalogBrandController::class, 'store'])->name('brands.store');
                Route::put('brands/{brand}', [CatalogBrandController::class, 'update'])->name('brands.update');
                Route::delete('brands/{brand}', [CatalogBrandController::class, 'destroy'])->name('brands.destroy');

                // Attributes (store-wide variant axes + their category links)
                Route::get('attributes', [CatalogAttributeController::class, 'index'])->name('attributes.index');
                Route::post('attributes', [CatalogAttributeController::class, 'store'])->name('attributes.store');
                Route::put('attributes/{attribute}', [CatalogAttributeController::class, 'update'])->name('attributes.update');
                Route::delete('attributes/{attribute}', [CatalogAttributeController::class, 'destroy'])->name('attributes.destroy');

                // Products (specific paths before the {product} wildcard)
                Route::get('products', [CatalogProductController::class, 'index'])->name('products.index');
                Route::get('products/create', [CatalogProductController::class, 'create'])->name('products.create');
                Route::get('products/lookup', [CatalogProductController::class, 'lookup'])->name('products.lookup');
                Route::get('products/option-suggestions', [CatalogProductController::class, 'optionSuggestions'])->name('products.option-suggestions');
                Route::post('products/images', [CatalogProductController::class, 'uploadImage'])->name('products.images.upload');
                Route::post('products', [CatalogProductController::class, 'store'])->name('products.store');
                Route::get('products/{product}/edit', [CatalogProductController::class, 'edit'])->name('products.edit');
                Route::put('products/{product}', [CatalogProductController::class, 'update'])->name('products.update');
                Route::delete('products/{product}', [CatalogProductController::class, 'destroy'])->name('products.destroy');
            });
        });
    });

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
