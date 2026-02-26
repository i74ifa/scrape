<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'password' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user = $request->user();

            if ($request->has('phone') && $request->phone !== $user->phone) {
                $user->phone = $request->phone;
                $user->phone_verified_at = null;
            }

            if ($request->has('name') && $request->name !== $user->name) {
                $user->name = $request->name;
            }

            // set first time password
            if ($user->password === null) {
                $user->password = bcrypt($request->password);
            }

            $user->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => trans('User updated failed'),
            ], 500);
        }

        return response()->json([
            'message' => trans('User updated successfully'),
            'user' => new UserResource($user),
        ]);
    }


    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $user = $request->user();

            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'message' => trans('Old password is incorrect'),
                ], 400);
            }

            $user->password = bcrypt($request->password);

            $user->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => trans('User password updated failed'),
            ], 500);
        }

        return response()->json([
            'message' => trans('User password updated successfully'),
            'user' => new UserResource($user),
        ]);
    }
}
