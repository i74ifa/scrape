<?php

namespace App\Http\Controllers\Api;

use App\RegexCode;
use App\Models\Otp;
use App\Models\User;
use App\Modules\M365Dialog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\SmsGateway;
use App\Services\Fcm\Fcm;
use App\Services\Fcm\FcmBody;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
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

        if ($regex === null) {
            return response()->json([
                'message' => trans('Invalid country code'),
            ], 422);
        }

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
            'expires_at' => now()->addMinutes(10),
            'valid' => true,
        ]);

        $template = <<<EOT
        رمز التحقق الخاص بك هو :otp

        لاتشاركه مع احد.
        EOT;

        if (app()->environment('production')) {
            SmsGateway::send(to: $phone, text: str_replace(':otp', $token, $template), countryCode: $countryCode);
        }

        return response()->json([
            'message' => trans('OTP sent successfully'),
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

        $isNew = false;
        $otpRecord->update(['valid' => false]);

        $user = User::where('phone', $phone)
            ->where('country_code', $countryCode)
            ->first();

        if (! $user) {
            $isNew = true;
            $user = User::create([
                'name' => null,
                'email' => null,
                'phone' => $phone,
                'country_code' => $countryCode,
                'password' => null,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->device_token = $request->device_token;
        $user->device_type = $request->device_type;
        $user->phone_verified_at = now();
        $user->save();

        try {
            $fcm = new Fcm();
            $fcm->send(new FcmBody([
                'token' => $request->device_token,
                'title' => 'ياهلا ومرحبا',
                'description' => 'حسابك عندنا، منتظرين اول طلب 🫰',
                'url' => '',
            ]));
        } catch (\Exception $th) {
            //throw $th;
        }

        return response()->json([
            'message' => trans('Authenticated successfully'),
            'user' => $user,
            'is_new' => $isNew,
            'token' => $token,
        ]);
    }
}
