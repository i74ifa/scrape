<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\URL;

use Laravel\Socialite\Socialite;

class AuthSocialController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
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
                'driver_type' => 'google',
                'driver_id' => $user->id,
            ]);

            $token = $newUser->createToken('google-login')->plainTextToken;
        }


        $signedUrl = URL::temporarySignedRoute(
            'login.success',
            now()->addMinutes(2),
            [
                'token' => $token,
                'email' => $user->email,
                'name' => $user->name,
            ]
        );

        return redirect($signedUrl);
    }

    public function redirectToTelegram()
    {
        return view('login-as-telegram');
    }

    public function handleTelegramCallback()
    {
        $user = Socialite::driver('telegram')->stateless()->user();

        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {
            $token = $existingUser->createToken('telegram-login')->plainTextToken;
        } else {
            $newUser = User::create([
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'driver_type' => 'telegram',
                'driver_id' => $user->id,
            ]);

            $token = $newUser->createToken('telegram-login')->plainTextToken;
        }

        $signedUrl = URL::temporarySignedRoute(
            'login.success',
            now()->addMinutes(2),
            [
                'token' => $token,
                'email' => $user->username,
                'name' => $user->first_name . ' ' . $user->last_name,
            ]
        );

        return redirect($signedUrl);
    }
}
