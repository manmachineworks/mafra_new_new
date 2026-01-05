<?php

namespace App\Http\Controllers;

use App\Services\ShiprocketService;

class ShiprocketTokenController extends Controller
{
    public function generate(ShiprocketService $shiprocket)
    {
        $result = $shiprocket->refreshToken();
        $status = $result['ok'] ? 200 : 422;

        return response()->json([
            'success' => $result['ok'],
            'message' => $result['message'] ?? '',
            'token' => $result['token'] ?? null,
        ], $status);
    }

    public function get(ShiprocketService $shiprocket)
    {
        $result = $shiprocket->getToken();
        $status = $result['ok'] ? 200 : 422;

        return response()->json([
            'success' => $result['ok'],
            'message' => $result['message'] ?? '',
            'token' => $result['token'] ?? null,
            'expires_at' => $result['expires_at'] ?? null,
        ], $status);
    }

    public function refresh(ShiprocketService $shiprocket)
    {
        $result = $shiprocket->refreshToken();

        $status = $result['ok'] ? 200 : 422;

        return response()->json([
            'success' => $result['ok'],
            'message' => $result['message'] ?? '',
        ], $status);
    }
}
