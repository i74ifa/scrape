<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            [
                [
                    'label' => 'Yemen',
                    'value' => 'YER',
                ],
                [
                    'label' => 'Saudi Arabia',
                    'value' => 'SAR',
                ],
            ]
        );
    }

    public function update(Request $request)
    {
        $request->validate([
            'currency' => 'required|in:YER,SAR',
        ]);

        user()->update([
            'currency' => $request->currency,
        ]);

        return response()->json([
            'message' => trans('Currency updated successfully'),
        ]);
    }
}
