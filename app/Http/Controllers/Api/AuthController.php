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
use App\Modules\WhatsappGateway;
use App\Services\Fcm\Fcm;
use App\Services\Fcm\FcmBody;
use Illuminate\Support\Facades\Hash;
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

        $user = User::where('phone', $phone)
            ->where('country_code', $countryCode)
            ->first();

        // if ($user) {
        //     return response()->json([
        //         'message' => trans('User already exists'),
        //         'is_new' => false,
        //     ], 200);
        // }

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

        $template = "رمز التحقق الخاص بك في تطبيق طلبي هو \n:otp\n\nلاتشاركه مع احد.";

        if (app()->environment('production')) {
            WhatsappGateway::sendMessage(to: $phone, message: str_replace(':otp', $token, $template), countryCode: $countryCode);
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
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->device_token = $request->device_token;
        $user->device_type = $request->device_type;
        $user->phone_verified_at = now();
        $user->save();

        try {
            $fcm = new Fcm();
            if ($isNew) {
                $fcm->send(new FcmBody([
                    'token' => $request->device_token,
                    'title' => 'ياهلا ومرحبا',
                    'description' => 'حسابك عندنا، منتظرين اول طلب 🫰',
                    'url' => '',
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
