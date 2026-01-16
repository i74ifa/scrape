<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        // Normalize identifier
        $identifier = $countryCode . $phone;

        // Generate OTP
        // For development/demo purposes we might want to log it or return it,
        // but for security usually we don't return it.
        // I will use a fixed OTP '1234' for easier testing if needed, or random.
        // Let's use random 4 digits.
        $token = (string) random_int(1000, 9999);

        // Invalidate previous OTPs
        Otp::where('identifier', $identifier)->update(['valid' => false]);

        // Create new OTP
        Otp::create([
            'identifier' => $identifier,
            'token' => $token,
            'expires_at' => now()->addMinutes(10),
            'valid' => true,
        ]);

        // TODO: Integrate SMS provider here.
        // For now we just return success.

        return response()->json([
            'message' => 'OTP sent successfully',
            // 'debug_otp' => $token, // Uncomment for debugging if needed
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string',
            'country_code' => 'nullable|string',
        ]);

        $phone = $request->input('phone');
        $otp = $request->input('otp');
        $countryCode = $request->input('country_code', '+967');
        $identifier = $countryCode . $phone;

        // Check OTP
        $otpRecord = Otp::where('identifier', $identifier)
            ->where('token', $otp)
            ->where('valid', true)
            ->where('expires_at', '>', now())
            ->first();

        if (! $otpRecord) {
            return response()->json([
                'message' => 'Invalid or expired OTP',
            ], 422);
        }

        // Invalidate OTP
        $otpRecord->update(['valid' => false]);

        // Find or create user
        $user = User::where('phone', $phone)
            ->where('country_code', $countryCode)
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => 'User ' . $phone,
                'email' => null, // Email is optional for phone users
                'phone' => $phone,
                'country_code' => $countryCode,
                'password' => null, // No password for OTP users
            ]);
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Authenticated successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }
}
