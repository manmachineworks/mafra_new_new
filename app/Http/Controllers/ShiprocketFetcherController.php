<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShiprocketFetcherController extends Controller
{
    public function fetchTracking(Order $order, ShiprocketService $shiprocket)
    {
        $result = $shiprocket->track($order);
        $status = $result['ok'] ? 200 : 422;

        return response()->json([
            'success' => $result['ok'],
            'message' => $result['message'] ?? '',
            'status' => $order->shiprocket_status,
            'awb' => $order->shiprocket_awb,
            'tracking' => $order->shiprocket_tracking_payload ? json_decode($order->shiprocket_tracking_payload, true) : null,
        ], $status);
    }

    public function fetchCouriers(Order $order, ShiprocketService $shiprocket)
    {
        $result = $shiprocket->fetchAvailableCouriers($order);
        $status = $result['ok'] ? 200 : 422;

        return response()->json([
            'success' => $result['ok'],
            'message' => $result['message'] ?? '',
            'data' => $result['response'] ?? [],
        ], $status);
    }

    public function fetchPickups(ShiprocketService $shiprocket)
    {
        $payload = Cache::remember('shiprocket_pickup_locations', now()->addMinutes(120), function () use ($shiprocket) {
            return $shiprocket->fetchPickupLocations();
        });

        $ok = $payload['ok'] ?? false;
        $status = $ok ? 200 : 422;

        return response()->json([
            'success' => $ok,
            'message' => $payload['message'] ?? '',
            'data' => $payload['response'] ?? [],
        ], $status);
    }

    public function fetchWallet(ShiprocketService $shiprocket)
    {
        $payload = Cache::remember('shiprocket_wallet_balance', now()->addMinutes(10), function () use ($shiprocket) {
            return $shiprocket->fetchWalletBalance();
        });

        $ok = $payload['ok'] ?? false;
        $status = $ok ? 200 : 422;

        return response()->json([
            'success' => $ok,
            'message' => $payload['message'] ?? '',
            'data' => $payload['response'] ?? [],
        ], $status);
    }

    public function generateLabel(Order $order, ShiprocketService $shiprocket)
    {
        $result = $shiprocket->generateLabel($order);
        $status = $result['ok'] ? 200 : 422;

        return response()->json([
            'success' => $result['ok'],
            'message' => $result['message'] ?? '',
            'url' => $result['url'] ?? null,
        ], $status);
    }

    public function generateInvoice(Order $order, ShiprocketService $shiprocket)
    {
        $result = $shiprocket->generateInvoice($order);
        $status = $result['ok'] ? 200 : 422;

        return response()->json([
            'success' => $result['ok'],
            'message' => $result['message'] ?? '',
            'url' => $result['url'] ?? null,
        ], $status);
    }

    public function cancelShipment(Request $request, Order $order, ShiprocketService $shiprocket)
    {
        $result = $shiprocket->cancelShipment($order, $request->input('reason', 'Cancelled from admin'));
        $status = $result['ok'] ? 200 : 422;

        return response()->json([
            'success' => $result['ok'],
            'message' => $result['message'] ?? '',
        ], $status);
    }
}
