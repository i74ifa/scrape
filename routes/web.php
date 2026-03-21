<?php

use Illuminate\Support\Facades\Route;



Route::get('auth/telegram', function () {
    return view('login-as-telegram');
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
