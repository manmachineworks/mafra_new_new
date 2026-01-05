<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShiprocketService
{
    private const TOKEN_CACHE_KEY = 'shiprocket_api_token';
    private const TOKEN_META_CACHE_KEY = 'shiprocket_api_token_meta';

    private string $baseUrl;
    /**
     * Map Shiprocket status strings to normalized timeline keys.
     */
    private array $statusMap = [
        'NEW' => 'ordered',
        'MANIFEST' => 'ordered',
        'MANIFESTED' => 'ordered',
        'PICKUP_GENERATED' => 'shipped',
        'PICKED_UP' => 'shipped',
        'IN_TRANSIT' => 'in_transit',
        'PENDING' => 'in_transit',
        'REACHED_AT_DESTINATION' => 'in_transit',
        'OUT_FOR_DELIVERY' => 'out_for_delivery',
        'DELIVERED' => 'delivered',
        'CANCELLED' => 'cancelled',
        'CANCELED' => 'cancelled',
        'RTO' => 'cancelled',
        'RTO_DELIVERED' => 'cancelled',
    ];

    public function __construct()
    {
        $this->baseUrl = rtrim(config('shiprocket.base_url'), '/');
    }

    public function pushOrder(Order $order, array $options = []): array
    {
        try {
            if ($order->shiprocket_shipment_id) {
                return [
                    'ok' => true,
                    'message' => 'Order already synced with Shiprocket',
                ];
            }
            if ($order->payment_status !== 'paid') {
                return [
                    'ok' => false,
                    'message' => 'Order must be paid before pushing to Shiprocket',
                ];
            }

            $order->loadMissing('orderDetails.product');

            $payload = $this->buildOrderPayload($order, $options);
            $response = $this->request('post', '/orders/create/adhoc', $payload);

            if (!$response->successful()) {
                $this->logFailure('Order push failed', $response, ['order_id' => $order->id]);
                return [
                    'ok' => false,
                    'message' => $response->json('message') ?? 'Shiprocket order creation failed',
                    'response' => $response->json(),
                ];
            }

            $data = $response->json();

            $order->shiprocket_order_id = $data['order_id'] ?? ($data['data']['order_id'] ?? null);
            $order->shiprocket_shipment_id = $data['shipment_id'] ?? ($data['data']['shipment_id'] ?? null);
            $order->shiprocket_awb = $data['awb_code'] ?? ($data['data']['awb_code'] ?? null);
            $order->shiprocket_status = $data['current_status'] ?? ($data['data']['status'] ?? 'created');
            $order->shiprocket_label_url = $data['label_url'] ?? ($data['data']['label_url'] ?? null);
            $order->shiprocket_manifest_url = $data['manifest_url'] ?? ($data['data']['manifest_url'] ?? null);
            $order->shiprocket_courier_name = $data['assigned_courier_name'] ?? ($data['data']['courier_name'] ?? null);
            $order->shiprocket_last_synced_at = now();
            if (empty($order->tracking_code) && $order->shiprocket_awb) {
                $order->tracking_code = $order->shiprocket_awb;
            }
            $order->save();

            return [
                'ok' => true,
                'message' => 'Order pushed to Shiprocket',
                'response' => $data,
            ];
        } catch (Throwable $e) {
            $this->logException('Order push exception', $e, ['order_id' => $order->id]);
            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function schedulePickup(Order $order, ?Carbon $pickupDate = null): array
    {
        if (!$order->shiprocket_shipment_id) {
            return ['ok' => false, 'message' => 'Shipment not created yet'];
        }

        try {
            $payload = [
                'shipment_id' => [$order->shiprocket_shipment_id],
                'pickup_date' => ($pickupDate ?? now())->toDateString(),
            ];

            $response = $this->request('post', '/courier/generate/pickup', $payload);
            if (!$response->successful()) {
                $this->logFailure('Pickup scheduling failed', $response, ['order_id' => $order->id]);
                return [
                    'ok' => false,
                    'message' => $response->json('message') ?? 'Shiprocket pickup scheduling failed',
                    'response' => $response->json(),
                ];
            }

            $order->shiprocket_pickup_scheduled_at = now();
            $order->shiprocket_status = $response->json('status') ?? $order->shiprocket_status;
            $order->save();

            return [
                'ok' => true,
                'message' => 'Pickup scheduled',
                'response' => $response->json(),
            ];
        } catch (Throwable $e) {
            $this->logException('Pickup scheduling exception', $e, ['order_id' => $order->id]);
            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function track(Order $order): array
    {
        if (!$order->shiprocket_awb) {
            return ['ok' => false, 'message' => 'AWB not available'];
        }

        // Serve cached payload if recently synced to avoid hammering the API.
        if ($order->shiprocket_tracking_payload && $order->shiprocket_last_synced_at && $order->shiprocket_last_synced_at->gt(now()->subMinutes(10))) {
            return [
                'ok' => true,
                'message' => 'Using cached tracking data',
                'response' => json_decode($order->shiprocket_tracking_payload, true),
            ];
        }

        try {
            $response = $this->request('get', '/courier/track/awb/' . $order->shiprocket_awb);

            if (!$response->successful()) {
                $this->logFailure('Tracking fetch failed', $response, ['order_id' => $order->id]);
                $this->applyFallbackStatus($order);
                return [
                    'ok' => false,
                    'message' => $response->json('message') ?? 'Shiprocket tracking fetch failed',
                    'response' => $response->json(),
                ];
            }

            $data = $response->json();
            $trackingData = $data['tracking_data'] ?? $data;
            $normalized = $this->normalizeTrackingStatus($trackingData);

            $order->shiprocket_status = $trackingData['shipment_status'] ?? ($trackingData['tracking_status'] ?? $order->shiprocket_status);
            $order->shiprocket_courier_name = $trackingData['courier_name'] ?? ($trackingData['assigned_courier_name'] ?? $order->shiprocket_courier_name);
            $order->shiprocket_awb = $trackingData['awb'] ?? ($trackingData['awb_code'] ?? $order->shiprocket_awb);
            if (isset($trackingData['track_url']) && empty($order->shiprocket_label_url)) {
                $order->shiprocket_label_url = $trackingData['track_url'];
            }

            $lastActivity = null;
            if (!empty($trackingData['shipment_track_activities']) && is_array($trackingData['shipment_track_activities'])) {
                $lastActivity = $trackingData['shipment_track_activities'][0];
            }

            $order->current_status = $normalized['status'];
            $order->status_code = $normalized['code'];
            $order->status_updated_at = $normalized['updated_at'] ?? now();
            $this->syncDeliveryStatus($order, $normalized['status']);

            $order->shiprocket_tracking_payload = json_encode([
                'status' => $order->shiprocket_status,
                'awb' => $order->shiprocket_awb,
                'courier' => $order->shiprocket_courier_name,
                'track_url' => $order->shiprocket_label_url ?? ($trackingData['track_url'] ?? null),
                'eta' => $trackingData['etd'] ?? $trackingData['etd_text'] ?? null,
                'last_activity' => $lastActivity,
                'raw' => $trackingData,
            ]);
            $order->shiprocket_last_synced_at = now();
            $order->save();

            return ['ok' => true, 'message' => 'Tracking updated', 'response' => $data];
        } catch (Throwable $e) {
            $this->logException('Tracking fetch exception', $e, ['order_id' => $order->id]);
            $this->applyFallbackStatus($order);
            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function handleWebhook(array $payload, ?string $token = null): array
    {
        $expectedToken = config('shiprocket.webhook_token');
        if ($expectedToken && $token !== $expectedToken) {
            return ['ok' => false, 'message' => 'Invalid webhook token'];
        }

        $awb = $payload['awb'] ?? $payload['awb_code'] ?? null;
        if (!$awb) {
            return ['ok' => false, 'message' => 'AWB missing in webhook'];
        }

        $order = Order::where('shiprocket_awb', $awb)->first();
        if (!$order) {
            return ['ok' => false, 'message' => 'Order not found for AWB'];
        }

        $order->shiprocket_status = $payload['status'] ?? ($payload['current_status'] ?? $order->shiprocket_status);
        if (!empty($payload['pickup_scheduled_date'])) {
            $order->shiprocket_pickup_scheduled_at = Carbon::parse($payload['pickup_scheduled_date']);
        }
        $order->shiprocket_tracking_payload = json_encode([
            'status' => $order->shiprocket_status,
            'awb' => $order->shiprocket_awb,
            'courier' => $order->shiprocket_courier_name,
            'last_activity' => [
                'activity' => $payload['status'] ?? $payload['current_status'] ?? null,
                'date' => now()->toDateTimeString(),
            ],
            'raw' => $payload,
        ]);
        $order->shiprocket_last_synced_at = now();
        $order->save();

        return ['ok' => true, 'message' => 'Webhook processed'];
    }

    private function buildOrderPayload(Order $order, array $options = []): array
    {
        $shippingAddress = json_decode($order->shipping_address ?? '{}', true) ?: [];
        $state = $shippingAddress['state'] ?? config('shiprocket.default_state', 'NA');
        $paymentMethod = ($order->payment_type === 'cash_on_delivery' || $order->payment_status !== 'paid') ? 'COD' : 'Prepaid';
        $weight = $this->calculateWeight($order);

        $items = $order->orderDetails->map(function ($detail) {
            $product = $detail->product;

            return [
                'name' => $product?->getTranslation('name') ?? $product?->name ?? 'Item',
                'sku' => $product?->sku ?? ('ITEM-' . $detail->id),
                'units' => $detail->quantity,
                'selling_price' => round($detail->price / max(1, $detail->quantity), 2),
                'discount' => 0,
                'tax' => round($detail->tax / max(1, $detail->quantity), 2),
                'hsn' => $this->sanitizeHsn($product?->hsn),
            ];
        })->values()->toArray();

        $totalDiscount = ($order->prepaid_discount_amount ?? 0) + ($order->coupon_discount ?? 0);
        
        return [
            'order_id' => $order->code,
            'order_date' => Carbon::createFromTimestamp($order->date)->format('Y-m-d H:i'),
            'pickup_location' => config('shiprocket.pickup_location'),
            'channel_id' => '',
            'comment' => $order->additional_info,
            'billing_customer_name' => $shippingAddress['name'] ?? $order->user->name,
            'billing_last_name' => '',
            'billing_address' => $shippingAddress['address'] ?? '',
            'billing_address_2' => $shippingAddress['address_2'] ?? '',
            'billing_city' => $shippingAddress['city'] ?? '',
            'billing_pincode' => $shippingAddress['postal_code'] ?? '',
            'billing_state' => $state ?: config('shiprocket.default_state', 'NA'),
            'billing_country' => $shippingAddress['country'] ?? 'India',
            'billing_email' => $shippingAddress['email'] ?? $order->user->email,
            'billing_phone' => $shippingAddress['phone'] ?? $order->user->phone,
            'shipping_is_billing' => true,
            'order_items' => $items,
            'total_discount' => max(0, round($totalDiscount, 2)),
            'payment_method' => $paymentMethod,
            'sub_total' => round($order->grand_total, 2),
            'length' => $options['length'] ?? config('shiprocket.default_length'),
            'breadth' => $options['breadth'] ?? config('shiprocket.default_breadth'),
            'height' => $options['height'] ?? config('shiprocket.default_height'),
            'weight' => $options['weight'] ?? $weight,
        ];
    }

    /**
     * Shiprocket requires numeric HSN with max length 15; default to "0" when unavailable.
     */
    private function sanitizeHsn($value): string
    {
        $digits = preg_replace('/\D/', '', (string) $value);
        $digits = $digits ? substr($digits, 0, 15) : '0';

        return $digits;
    }

    private function calculateWeight(Order $order): float
    {
        $total = 0;
        foreach ($order->orderDetails as $detail) {
            $productWeight = optional($detail->product)->weight ?? config('shiprocket.default_weight');
            $total += ($productWeight ?: config('shiprocket.default_weight')) * $detail->quantity;
        }

        return round(max($total, config('shiprocket.default_weight')), 3);
    }

    private function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout(config('shiprocket.timeout'))
            ->withToken($this->token())
            ->acceptJson();
    }

    private function request(string $method, string $uri, array $payload = [], array $query = [])
    {
        $response = $this->performRequest($method, $uri, $payload, $query);

        // Retry once on 401 with a refreshed token.
        if ($response->status() === 401) {
            $refresh = $this->refreshToken();
            if (!empty($refresh['ok'])) {
                $response = $this->performRequest($method, $uri, $payload, $query);
            }
        }

        return $response;
    }

    private function performRequest(string $method, string $uri, array $payload, array $query)
    {
        $client = Http::baseUrl($this->baseUrl)
            ->timeout(config('shiprocket.timeout'))
            ->withToken($this->token())
            ->acceptJson();

        if ($method === 'get') {
            return $client->get($uri, empty($query) ? $payload : $query);
        }

        return $client->{$method}($uri, $payload);
    }

    private function token(bool $forceRefresh = false): string
    {
        $meta = Cache::get(self::TOKEN_META_CACHE_KEY);
        $cached = Cache::get(self::TOKEN_CACHE_KEY);

        if (!$forceRefresh && $cached && $meta && isset($meta['expires_at'])) {
            if (Carbon::parse($meta['expires_at'])->gt(now()->addMinutes(5))) {
                return $cached;
            }
        }

        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(50), function () use ($forceRefresh) {
            if ($forceRefresh) {
                Cache::forget(self::TOKEN_CACHE_KEY);
                Cache::forget(self::TOKEN_META_CACHE_KEY);
            }

            $payload = [
                'email' => config('shiprocket.email'),
                'password' => config('shiprocket.password'),
            ];

            if (config('shiprocket.api_key') && config('shiprocket.api_secret')) {
                $payload['api_key'] = config('shiprocket.api_key');
                $payload['api_secret'] = config('shiprocket.api_secret');
            }

            $response = Http::baseUrl($this->baseUrl)
                ->timeout(config('shiprocket.timeout'))
                ->post('/auth/login', $payload);

            if (!$response->successful()) {
                $this->logFailure('Shiprocket auth failed', $response);
                throw new \RuntimeException('Shiprocket authentication failed');
            }

            $token = $response->json('token');
            if (!$token) {
                throw new \RuntimeException('Shiprocket token missing from response');
            }

            $expiresSeconds = (int) ($response->json('expires_in') ?? 3000);
            $expiresAt = now()->addSeconds($expiresSeconds);

            Cache::put(self::TOKEN_CACHE_KEY, $token, $expiresAt);
            Cache::put(self::TOKEN_META_CACHE_KEY, ['expires_at' => $expiresAt], $expiresAt);

            return $token;
        });
    }

    public function getToken(): array
    {
        try {
            $token = $this->token();
            $meta = Cache::get(self::TOKEN_META_CACHE_KEY);

            return [
                'ok' => true,
                'token' => $token,
                'expires_at' => isset($meta['expires_at']) ? Carbon::parse($meta['expires_at'])->toDateTimeString() : null,
            ];
        } catch (Throwable $e) {
            $this->logException('Shiprocket token fetch failed', $e);
            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function refreshToken(): array
    {
        try {
            Cache::forget(self::TOKEN_CACHE_KEY);
            Cache::forget(self::TOKEN_META_CACHE_KEY);
            $token = $this->token(true);

            return [
                'ok' => true,
                'message' => 'Shiprocket token refreshed',
                'token' => $token,
            ];
        } catch (Throwable $e) {
            $this->logException('Shiprocket token refresh failed', $e);
            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function fetchTrackingByAwb(string $awb): array
    {
        try {
            $response = $this->request('get', '/courier/track/awb/' . $awb);
            if (!$response->successful()) {
                $this->logFailure('Shiprocket tracking fetch failed', $response, ['awb' => $awb]);
                return ['ok' => false, 'message' => $response->json('message') ?? 'Tracking fetch failed', 'response' => $response->json()];
            }

            return ['ok' => true, 'response' => $response->json()];
        } catch (Throwable $e) {
            $this->logException('Shiprocket tracking fetch exception', $e, ['awb' => $awb]);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function fetchAvailableCouriers(Order $order, array $overrides = []): array
    {
        $address = json_decode($order->shipping_address ?? '{}', true) ?: [];

        $payload = array_merge([
            'pickup_postcode' => $address['postal_code'] ?? '',
            'delivery_postcode' => $address['postal_code'] ?? '',
            'cod' => $order->payment_type === 'cash_on_delivery' ? 1 : 0,
            'weight' => $overrides['weight'] ?? $this->calculateWeight($order),
            'mode' => $overrides['mode'] ?? 'Surface',
        ], $overrides);

        try {
            $response = $this->request('get', '/courier/serviceability', $payload);
            if (!$response->successful()) {
                $this->logFailure('Shiprocket courier serviceability failed', $response, ['order_id' => $order->id]);
                return ['ok' => false, 'message' => $response->json('message') ?? 'Could not fetch couriers', 'response' => $response->json()];
            }

            return ['ok' => true, 'response' => $response->json()];
        } catch (Throwable $e) {
            $this->logException('Shiprocket courier serviceability exception', $e, ['order_id' => $order->id]);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function fetchPickupLocations(): array
    {
        try {
            $response = $this->request('get', '/settings/company/pickup');
            if (!$response->successful()) {
                $this->logFailure('Shiprocket pickup locations failed', $response, []);
                return ['ok' => false, 'message' => $response->json('message') ?? 'Could not fetch pickup locations', 'response' => $response->json()];
            }

            return ['ok' => true, 'response' => $response->json()];
        } catch (Throwable $e) {
            $this->logException('Shiprocket pickup fetch exception', $e);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function fetchWalletBalance(): array
    {
        try {
            $response = $this->request('get', '/wallet/balance');
            if (!$response->successful()) {
                $this->logFailure('Shiprocket wallet fetch failed', $response, []);
                return ['ok' => false, 'message' => $response->json('message') ?? 'Could not fetch wallet balance', 'response' => $response->json()];
            }

            return ['ok' => true, 'response' => $response->json()];
        } catch (Throwable $e) {
            $this->logException('Shiprocket wallet fetch exception', $e);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function generateLabel(Order $order): array
    {
        if (!$order->shiprocket_shipment_id) {
            return ['ok' => false, 'message' => 'Shipment ID missing'];
        }

        try {
            $response = $this->request('post', '/courier/generate/label', [
                'shipment_id' => [$order->shiprocket_shipment_id],
            ]);

            if (!$response->successful()) {
                $this->logFailure('Shiprocket label generation failed', $response, ['order_id' => $order->id]);
                return ['ok' => false, 'message' => $response->json('message') ?? 'Label generation failed', 'response' => $response->json()];
            }

            $url = $response->json('label_url') ?? ($response->json('data.label_url') ?? null);
            if ($url) {
                $order->shiprocket_label_url = $url;
                $order->shiprocket_last_synced_at = now();
                $order->save();
            }

            return ['ok' => true, 'message' => 'Label generated', 'response' => $response->json(), 'url' => $url];
        } catch (Throwable $e) {
            $this->logException('Shiprocket label generation exception', $e, ['order_id' => $order->id]);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function generateInvoice(Order $order): array
    {
        if (!$order->shiprocket_order_id) {
            return ['ok' => false, 'message' => 'Order ID missing for invoice'];
        }

        try {
            $response = $this->request('post', '/orders/print/invoice', [
                'ids' => [$order->shiprocket_order_id],
            ]);

            if (!$response->successful()) {
                $this->logFailure('Shiprocket invoice generation failed', $response, ['order_id' => $order->id]);
                return ['ok' => false, 'message' => $response->json('message') ?? 'Invoice generation failed', 'response' => $response->json()];
            }

            $url = $response->json('invoice_url') ?? ($response->json('data.invoice_url') ?? null);
            if ($url) {
                $order->shiprocket_manifest_url = $url;
                $order->shiprocket_last_synced_at = now();
                $order->save();
            }

            return ['ok' => true, 'message' => 'Invoice generated', 'response' => $response->json(), 'url' => $url];
        } catch (Throwable $e) {
            $this->logException('Shiprocket invoice generation exception', $e, ['order_id' => $order->id]);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelShipment(Order $order, string $reason = ''): array
    {
        if (!$order->shiprocket_order_id) {
            return ['ok' => false, 'message' => 'Order not pushed to Shiprocket'];
        }

        try {
            $response = $this->request('post', '/orders/cancel', [
                'ids' => [$order->shiprocket_order_id],
                'reason' => $reason ?: 'Cancelled from admin',
            ]);

            if (!$response->successful()) {
                $this->logFailure('Shiprocket cancellation failed', $response, ['order_id' => $order->id]);
                return ['ok' => false, 'message' => $response->json('message') ?? 'Cancellation failed', 'response' => $response->json()];
            }

            $order->shiprocket_status = 'cancelled';
            $order->shiprocket_last_synced_at = now();
            $order->save();

            return ['ok' => true, 'message' => 'Shipment cancelled', 'response' => $response->json()];
        } catch (Throwable $e) {
            $this->logException('Shiprocket cancellation exception', $e, ['order_id' => $order->id]);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function logFailure(string $message, $response, array $context = []): void
    {
        Log::channel(config('shiprocket.log_channel'))
            ->error($message, array_merge($context, [
                'status' => $response->status(),
                'body' => $response->json(),
            ]));
    }

    private function logException(string $message, Throwable $e, array $context = []): void
    {
        Log::channel(config('shiprocket.log_channel'))
            ->error($message, array_merge($context, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]));
    }

    private function normalizeTrackingStatus(array $trackingData): array
    {
        $rawStatus = strtoupper($trackingData['current_status'] ?? $trackingData['shipment_status'] ?? $trackingData['tracking_status'] ?? '');
        $mapped = $this->statusMap[$rawStatus] ?? 'in_transit';

        $lastEvent = null;
        if (!empty($trackingData['shipment_track_activities'][0])) {
            $lastEvent = $trackingData['shipment_track_activities'][0];
        }

        return [
            'status' => $mapped,
            'code' => $rawStatus ?: 'IN_TRANSIT',
            'updated_at' => isset($lastEvent['date']) ? Carbon::parse($lastEvent['date']) : now(),
        ];
    }

    private function applyFallbackStatus(Order $order): void
    {
        $order->current_status = $order->current_status ?? 'in_transit';
        $order->status_code = $order->status_code ?? 'IN_TRANSIT';
        $order->status_updated_at = $order->status_updated_at ?? now();
        $this->syncDeliveryStatus($order, $order->current_status);
        $order->save();
    }

    private function syncDeliveryStatus(Order $order, string $normalized): void
    {
        $map = [
            'ordered' => 'confirmed',
            'shipped' => 'picked_up',
            'in_transit' => 'on_the_way',
            'out_for_delivery' => 'on_the_way',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
        ];

        if (!isset($map[$normalized])) {
            return;
        }

        $new = $map[$normalized];
        if ($order->delivery_status !== 'delivered' && $order->delivery_status !== 'cancelled') {
            $order->delivery_status = $new;
        }
    }
}
