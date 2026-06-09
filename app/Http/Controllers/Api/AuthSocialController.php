<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
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

        // redirect to talabye://login?token=...&email=...&name=...
        $url = "talabye://login?token={$token}&email={$user->email}&name={$user->name}";
        return redirect()->away($url);
    }

    public function redirectToApple()
    {
        return Socialite::driver('apple')->stateless()->redirect();
    }

    public function handleAppleCallback()
    {
        $user = Socialite::driver('apple')->stateless()->user();

        // Apple only returns the name on the first authorization, and the
        // email may be a private relay address, so match on the stable
        // Apple subject id first and fall back to the email.
        $existingUser = User::where('driver_id', $user->id)
            ->when($user->email, function ($query) use ($user) {
                $query->orWhere('email', $user->email);
            })
            ->first();

        if ($existingUser) {
            $token = $existingUser->createToken('apple-login')->plainTextToken;

            if (empty($existingUser->email) && ! empty($user->email)) {
                $existingUser->email = $user->email;
            }

            if (empty($existingUser->name) && ! empty($user->name)) {
                $existingUser->name = $user->name;
            }

            $existingUser->save();
        } else {
            $newUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
                'driver_type' => 'apple',
                'driver_id' => $user->id,
            ]);

            $token = $newUser->createToken('apple-login')->plainTextToken;
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

        // redirect to talabye://login?token=...&email=...&name=...
        $url = "talabye://login?token={$token}&email={$user->email}&name={$user->name}";
        return redirect()->away($url);
    }

    public function redirectToTelegram()
    {
        return view('login-as-telegram');
    }

    public function handleTelegramCallback()
    {
        $user = Socialite::driver('telegram')->stateless()->user();

        $existingUser = User::where('driver_id', $user->id)->first();

        if ($existingUser) {
            $token = $existingUser->createToken('telegram-login')->plainTextToken;
            if (empty($existingUser->email)) {
                $existingUser->email = $user->username;
            }

            if (empty($existingUser->name)) {
                $existingUser->name = $user->nickname;
            }

            $existingUser->save();
        } else {
            $newUser = User::create([
                'name' => $user->nickname,
                'email' => $user->username,
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
                'name' => $user->nickname,
            ]
        );

        // redirect to talabye://login?token=...&email=...&name=...
        $url = "talabye://login?token={$token}&email={$user->username}&name={$user->first_name} {$user->last_name}";
        return redirect()->away($url);
    }
}
