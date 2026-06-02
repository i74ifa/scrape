<?php

namespace App\Http\Controllers\Api;

use App\RegexCode;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\SendOtpJob;
use App\Modules\Massaging\Contracts\MessageSender;
use App\Services\Fcm\Fcm;
use App\Services\Fcm\FcmBody;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function sendOtp(Request $request, MessageSender $sender)
    {
        $request->validate([
            'phone' => 'required|string',
            'country_code' => 'nullable|string',
        ]);

        $phone = $request->input('phone');
        $countryCode = $request->input('country_code', '+967');
        $countryCode = RegexCode::getPhoneCountryCode($countryCode);

        $regex = RegexCode::getCountryRegexUsingCode($countryCode);
        $identifier = sprintf('%s%s', $countryCode, $phone);

        $user = User::where('phone', $phone)
            ->where('country_code', $countryCode)
            ->first();

        if ($user) {
            return response()->json([
                'message' => trans('User already exists'),
                'is_new' => false,
            ], 200);
        }

        if ($regex === null) {
            return response()->json([
                'message' => trans('Invalid country code'),
            ], 422);
        }

        $attemptKey = 'send-otp-attempts:' . $request->ip();
        $sentKey = 'send-otp-sent:' . $identifier;

        // An OTP was already sent to this number within the last minute → strict 1/min.
        if (RateLimiter::tooManyAttempts($sentKey, 1)) {
            $seconds = RateLimiter::availableIn($sentKey);
            return response()->json(['message' => "يرجى الانتظار $seconds ثانية"], 429);
        }

        // Otherwise allow up to 5 attempts/min (invalid phone, retries, etc.).
        if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
            $seconds = RateLimiter::availableIn($attemptKey);
            return response()->json(['message' => "يرجى الانتظار $seconds ثانية"], 429);
        }

        RateLimiter::hit($attemptKey, 60);

        if (! preg_match($regex, '+' . $identifier)) {
            return response()->json([
                'message' => trans('Invalid phone number'),
            ], 422);
        }

        $token = User::generateOtpToken();

        Otp::where('identifier', $identifier)->update(['valid' => false]);

        Otp::create([
            'identifier' => $identifier,
            'token' => $token,
            'expires_at' => now()->addMinutes(5),
            'valid' => true,
        ]);

        try {
            $randomSeconds = rand(5, 10);
            SendOtpJob::dispatch($phone, $token, $countryCode)->delay(now()->addSeconds($randomSeconds));

            // Only now that an OTP actually went out, enforce the strict 1/min limit.
            RateLimiter::hit($sentKey, 60);
        } catch (\Exception $th) {
            return response()->json([
                'message' => 'Failed to send OTP',
            ], 500);
        }

        return response()->json([
            'message' => trans('OTP sent successfully'),
            'is_new' => true,
        ]);
    }

    public function loginAsPassword(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|string',
                'password' => 'required|string',
                'country_code' => 'nullable|string',
                'device_token' => 'nullable|string',
                'device_type' => 'nullable|in:android,ios,ipados',
            ]);
        } catch (ValidationException $th) {
            return response()->json([
                'message' => $th->errors(),
            ], 422);
        }

        $phone = $request->input('phone');
        $countryCode = $request->input('country_code', '+967');
        $countryCode = RegexCode::getPhoneCountryCode($countryCode);
        $identifier = sprintf('%s%s', $countryCode, $phone);

        $user = User::where('phone', $phone)
            ->where('country_code', $countryCode)
            ->first();

        if (! $user) {
            return response()->json([
                'message' => trans('User not found'),
            ], 422);
        }

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => trans('Invalid password'),
            ], 422);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->device_token = $request->device_token;
        $user->device_type = $request->device_type;
        $user->phone_verified_at = now();
        $user->save();

        return response()->json([
            'message' => trans('Authenticated successfully'),
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|string',
                'otp' => 'required|numeric|digits:4',
                'country_code' => 'nullable|string',
                'device_token' => 'nullable|string',
                'device_type' => 'nullable|in:android,ios,ipados',
            ]);
        } catch (ValidationException $th) {
            return response()->json([
                'message' => $th->errors(),
            ], 422);
        }

        $phone = $request->input('phone');
        $otp = $request->input('otp');
        $countryCode = $request->input('country_code', '967');
        $countryCode = RegexCode::getPhoneCountryCode($countryCode);

        $identifier = sprintf('%s%s', $countryCode, $phone);
        $otpRecord = Otp::where('identifier', $identifier)
            ->where('token', $otp)
            ->where('valid', true)
            ->where('expires_at', '>', now())
            ->first();

        if (! $otpRecord) {
            return response()->json([
                'message' => trans('Invalid or expired OTP'),
            ], 422);
        }

        $otpRecord->update(['valid' => false]);

        $user = User::where('phone', $phone)
            ->where('country_code', $countryCode)
            ->first();

        $isNew = false;
        if (! $user) {
            $isNew = true;
            $user = User::create([
                'name' => null,
                'email' => null,
                'phone' => $phone,
                'country_code' => $countryCode,
                'password' => null,
                'currency' => 'YER',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->device_token = $request->device_token;
        $user->device_type = $request->device_type;
        $user->phone_verified_at = now();
        $user->save();

        $user->increment('notification_badges');
        try {
            $fcm = new Fcm();
            if ($isNew) {
                $fcm->send(new FcmBody([
                    'token' => $request->device_token,
                    'title' => 'ياهلا ومرحبا',
                    'description' => 'حسابك صار عندنا، منتظرين اول طلب 🫰',
                    'url' => '',
                    'sound' => 'default',
                    'badge' => $user->notification_badges,
                ]));
            }
        } catch (\Exception $th) {
            //throw $th;
        }

        return response()->json([
            'message' => trans('Authenticated successfully'),
            'user' => $user,
            'token' => $token,
            'is_new' => $isNew,
        ]);
    }
}
