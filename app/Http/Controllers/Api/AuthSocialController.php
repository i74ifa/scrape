<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

use Laravel\Socialite\Socialite;
use SocialiteProviders\Apple\Provider as AppleProvider;

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

    /**
     * Native "Sign in with Apple" for the mobile app.
     *
     * The Flutter app authenticates with Apple natively (no webview) using the
     * sign_in_with_apple package and posts the resulting identity token here.
     * We verify that JWT against Apple's public keys and issue our own token.
     *
     * Expected JSON body:
     *   identity_token : string   (required) Apple credential.identityToken
     *   name           : string   (optional) only present on first sign-in
     *   email          : string   (optional) only present on first sign-in
     */
    public function handleAppleNative(Request $request)
    {
        $data = $request->validate([
            'identity_token' => 'required|string',
            'device_token' => 'nullable|string',
            'name' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        // Verifies signature, issuer and expiry against Apple's public keys,
        // then returns a Socialite user whose id is the stable Apple subject.
        $appleUser = Socialite::driver('apple')->userByIdentityToken($data['identity_token']);

        // Apple only returns name/email on the very first authorization, so the
        // app forwards them in the body as a fallback to the token claims.
        $email = $appleUser->email ?: ($data['email'] ?? null);
        $name = $appleUser->name ?: ($data['name'] ?? null);

        // Match on the stable Apple subject id first, fall back to email.
        $user = User::where('driver_id', $appleUser->id)
            ->when($email, function ($query) use ($email) {
                $query->orWhere('email', $email);
            })
            ->first();

        if ($user) {
            if (empty($user->driver_id)) {
                $user->driver_id = $appleUser->id;
                $user->driver_type = 'apple';
            }

            if (empty($user->email) && ! empty($email)) {
                $user->email = $email;
            }

            if (empty($user->name) && ! empty($name)) {
                $user->name = $name;
            }

            $user->device_token = $data['device_token'] ?: $user->device_token ?: null;

            $user->save();
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'driver_type' => 'apple',
                'driver_id' => $appleUser->id,
                'device_token' => $data['device_token'] ?: null,
            ]);
        }

        $token = $user->createToken('apple-login')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
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
