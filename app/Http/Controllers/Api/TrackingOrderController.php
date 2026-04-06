<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;

class TrackingOrderController extends Controller
{
    public function index($trackingNumber)
    {
        $order = Order::where('code', $trackingNumber)->first();

        if (! $order) {
            return response()->json([
                'message' => __('Order Tracking Number Not Found'),
            ], 404);
        }
        $statusHistory = OrderStatus::getTimelines($order->status_history, $order->platform);
        $statusHistory = collect($statusHistory)->filter(fn($s) => $s['status'] !== 'pending')->values();

        return response()->json([
            'id'             => $order->code,
            'order'          => $order,
            'status_history' => $statusHistory,
            'percentage'     => $order->status->percentage(),
            'current_status' => $order->status->title(),
            'current_status_icon' => $order->status->icon(),
            'next_status' => $order->status->next() ? $order->status->next() : $order->status,
            'next_status_icon' => $order->status->next() ? $order->status->next()->icon() : $order->status->icon(),
            'next_status_message' => $order->status->next() ? $order->status->next()->title() : $order->status->title(),
        ]);
    }
}
