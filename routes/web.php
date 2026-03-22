<?php

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

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
