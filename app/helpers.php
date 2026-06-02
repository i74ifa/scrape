<?php

if (!function_exists('user')) {
    function user($attribute = null, $guard = 'sanctum'): mixed
    {
        if ($attribute) {
            return auth($guard)->user()->$attribute;
        }

        return auth($guard)->user();
    }
}

if (!function_exists('money')) {
    /**
     * Wrap a stored amount as Money for display in the current user's currency.
     *
     * Prices are stored in the default currency (SAR); the active currency is
     * set per request from the user. Both ends of the conversion are therefore
     * implicit, so callers only pass the raw amount, e.g. money($cart->total).
     */
    function money($amount): \App\Services\Money
    {
        return \App\Services\Currency::convert($amount);
    }
}
