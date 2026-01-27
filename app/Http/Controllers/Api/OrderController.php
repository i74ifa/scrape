<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // get user orders
        $user = auth()->user();
        $orders = $user->orders()->with('items')->paginate(10);

        return OrderResource::collection($orders);
    }
}
