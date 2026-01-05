<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class Order extends Model
{
    use PreventDemoModeChanges;

    protected $casts = [
        'shiprocket_pickup_scheduled_at' => 'datetime',
        'shiprocket_last_synced_at' => 'datetime',
    ];
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function refund_requests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'user_id', 'seller_id');
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function club_point()
    {
        return $this->hasMany(ClubPoint::class);
    }

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'assign_delivery_boy', 'id');
    }

    public function proxy_cart_reference_id()
    {
        return $this->hasMany(ProxyPayment::class)->select('reference_id');
    }

    public function commissionHistory()
    {
        return $this->hasOne(CommissionHistory::class);
    }

    public function isShiprocketPushed(): bool
    {
        return !empty($this->shiprocket_shipment_id);
    }

    public function isShipmentLive(): bool
    {
        return !empty($this->shiprocket_awb) && !in_array($this->shiprocket_status, ['cancelled', 'cancelled_by_user']);
    }

    public function getTrackingUrl(): ?string
    {
        if (!empty($this->shiprocket_label_url)) {
            return $this->shiprocket_label_url;
        }

        if (!empty($this->shiprocket_tracking_payload)) {
            $payload = json_decode($this->shiprocket_tracking_payload, true);
            return $payload['track_url'] ?? null;
        }

        return null;
    }

    public function trackingStatus(): string
    {
        if (!empty($this->current_status)) {
            return $this->current_status;
        }

        $raw = strtoupper($this->shiprocket_status ?? '');
        $map = [
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

        if (isset($map[$raw])) {
            return $map[$raw];
        }

        if ($this->delivery_status === 'delivered') {
            return 'delivered';
        }

        if ($this->delivery_status === 'cancelled') {
            return 'cancelled';
        }

        return 'in_transit';
    }

    public function trackingStatusLabel(): string
    {
        $labels = [
            'ordered' => 'Ordered',
            'shipped' => 'Shipped',
            'in_transit' => 'In-Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];

        $key = $this->trackingStatus();

        return $labels[$key] ?? ucfirst($key);
    }
}
