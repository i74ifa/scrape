<?php

if (!function_exists('user')) {
    function user($attribute = null, $guard = 'api'): mixed
    {
        if ($attribute) {
            return auth($guard)->user()->$attribute;
        }

        return auth($guard)->user();
    }
}
