<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums;

class PaymentController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Enums\PaymentMethod::all(),
        ]);
    }
}
