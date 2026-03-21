<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Socialite;

class AuthSocialController extends Controller
{
    public function redirectToGoogle()
    {
        return [
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl()
        ];
    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->stateless()->user();

        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {
            $token = $existingUser->createToken('google-login')->plainTextToken;
        } else {
            $newUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
            ]);

            $token = $newUser->createToken('google-login')->plainTextToken;
        }

        return response()->json([
            'token' => $token,
            'user' => $existingUser ?? $newUser,
        ]);
    }

    public function redirectToTelegram()
    {
        return Socialite::driver('telegram')->stateless()->redirect();
    }

    public function handleTelegramCallback()
    {
        $user = Socialite::driver('telegram')->stateless()->user();

        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {
            $token = $existingUser->createToken('telegram-login')->plainTextToken;
        } else {
            $newUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
            ]);

            $token = $newUser->createToken('telegram-login')->plainTextToken;
        }

        return response()->json([
            'token' => $token,
            'user' => $existingUser ?? $newUser,
        ]);
    }
}
