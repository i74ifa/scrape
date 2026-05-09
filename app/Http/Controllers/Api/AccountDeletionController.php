<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendOtpJob;
use App\Models\Otp;
use App\Models\User;
use App\RegexCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AccountDeletionController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'country_code' => 'nullable|string',
        ]);

        $phone = $request->input('phone');
        $countryCode = RegexCode::getPhoneCountryCode($request->input('country_code', '+967'));
        $regex = RegexCode::getCountryRegexUsingCode($countryCode);
        $identifier = sprintf('%s%s', $countryCode, $phone);

        if ($regex === null) {
            return response()->json(['message' => trans('Invalid country code')], 422);
        }

        if (! preg_match($regex, '+' . $identifier)) {
            return response()->json(['message' => trans('Invalid phone number')], 422);
        }

        $key = 'account-delete-otp:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 2)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json(['message' => "يرجى الانتظار $seconds ثانية"], 429);
        }
        RateLimiter::hit($key, 60);

        $user = User::where('phone', $phone)
            ->where('country_code', $countryCode)
            ->first();

        if (! $user) {
            return response()->json(['message' => trans('User not found')], 404);
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
            SendOtpJob::dispatch($phone, $token, $countryCode)->delay(now()->addSeconds(rand(5, 10)));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send OTP'], 500);
        }

        return response()->json(['message' => trans('OTP sent successfully')]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|numeric|digits:4',
            'country_code' => 'nullable|string',
        ]);

        $phone = $request->input('phone');
        $countryCode = RegexCode::getPhoneCountryCode($request->input('country_code', '+967'));
        $identifier = sprintf('%s%s', $countryCode, $phone);

        $otpRecord = Otp::where('identifier', $identifier)
            ->where('token', $request->input('otp'))
            ->where('valid', true)
            ->where('expires_at', '>', now())
            ->first();

        if (! $otpRecord) {
            return response()->json(['message' => trans('Invalid or expired OTP')], 422);
        }

        $user = User::where('phone', $phone)
            ->where('country_code', $countryCode)
            ->first();

        if (! $user) {
            return response()->json(['message' => trans('User not found')], 404);
        }

        $otpRecord->update(['valid' => false]);
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => trans('Account scheduled for deletion. All data will be permanently removed within 30 days.'),
        ]);
    }
}
