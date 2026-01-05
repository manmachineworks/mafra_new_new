<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderTrackingController extends Controller
{
    public function show(Order $order, Request $request, ShiprocketService $shiprocket)
    {
        $this->authorizeView($order, $request);

        $shiprocket->track($order);

        $tracking = $order->shiprocket_tracking_payload ? json_decode($order->shiprocket_tracking_payload, true) : null;
        $history = $tracking['raw']['shipment_track_activities'] ?? [];

        return response()->json([
            'success' => true,
            'status' => $order->trackingStatus(),
            'status_label' => $order->trackingStatusLabel(),
            'status_code' => $order->status_code,
            'status_updated_at' => optional($order->status_updated_at)->toDateTimeString(),
            'delivery_status' => $order->delivery_status,
            'awb' => $order->shiprocket_awb,
            'courier' => $order->shiprocket_courier_name,
            'eta' => $tracking['eta'] ?? null,
            'track_url' => $tracking['track_url'] ?? $order->getTrackingUrl(),
            'last_activity' => $tracking['last_activity'] ?? null,
            'history' => $history,
            'fallback' => $tracking ? false : true,
        ]);
    }

    private function authorizeView(Order $order, Request $request): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (!in_array($user->user_type, ['admin', 'staff']) && $order->user_id !== $user->id) {
                abort(403);
            }
        } else {
            if ($request->input('order_code') !== $order->code) {
                abort(403);
            }
        }
    }
}
