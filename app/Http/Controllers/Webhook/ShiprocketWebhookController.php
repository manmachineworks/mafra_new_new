<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiprocketWebhookController extends Controller
{
    public function handle(Request $request, ShiprocketService $shiprocket)
    {
        $token = $request->header('X-Shiprocket-Token') ?? $request->input('token');
        $result = $shiprocket->handleWebhook($request->all(), $token);

        if (!$result['ok']) {
            Log::warning('Shiprocket webhook rejected', [
                'message' => $result['message'] ?? 'Unknown',
                'payload' => $request->all(),
            ]);

            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json(['message' => 'ok']);
    }
}
