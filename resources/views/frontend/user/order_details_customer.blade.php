@extends('frontend.layouts.user_panel')

@section('panel_content')
    <!-- Order id -->
    <div class="aiz-titlebar mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="fs-20 fw-700 text-dark">{{ translate('Order id') }}: {{ $order->code }}</h1>
            </div>
        </div>
    </div>

    @php
        $shippingAddress = json_decode($order->shipping_address ?? '{}');
        $timelineSteps = [
            ['key' => 'ordered', 'label' => translate('Ordered')],
            ['key' => 'shipped', 'label' => translate('Shipped')],
            ['key' => 'in_transit', 'label' => translate('In-Transit')],
            ['key' => 'out_for_delivery', 'label' => translate('Out for Delivery')],
            ['key' => 'delivered', 'label' => translate('Delivered')],
        ];
        $currentKey = $order->trackingStatus();
        $currentIndex = collect($timelineSteps)->search(fn ($step) => $step['key'] === $currentKey);
        $trackingPayload = $order->shiprocket_tracking_payload ? json_decode($order->shiprocket_tracking_payload, true) : null;
        $etaText = $trackingPayload['eta'] ?? null;
        $lastActivity = $trackingPayload['last_activity'] ?? null;
    @endphp

    <div class="card rounded-0 shadow-none border mb-4" style="background:#ffffff;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <div>
                    <div class="fs-18 fw-700 text-dark">{{ translate('Tracking Status') }}</div>
                    <div class="text-muted small">{{ $lastActivity['activity'] ?? translate('Live shipment updates') }}</div>
                </div>
                <div class="text-right">
                    <span class=" badge-primary px-2 py-1">{{ $order->trackingStatusLabel() }}</span>
                    @if ($etaText)
                        <div class="text-success fw-700 mt-1">{{ translate('ETA') }}: <span id="tracking-eta">{{ $etaText }}</span></div>
                    @else
                        <div class="text-muted small" id="tracking-eta">{{ translate('Tracking update will appear soon') }}</div>
                    @endif
                </div>
            </div>
            <div class="position-relative" style="padding: 20px 0;">
                <div class="progress" style="height:6px; background:#e5e7eb;">
                    <div id="tracking-progress-bar" class="progress-bar bg-primary" role="progressbar"
                        style="width: {{ (($currentIndex !== false ? $currentIndex : 2) / max(count($timelineSteps)-1,1)) * 100 }}%; transition: width .4s ease;"></div>
                </div>
                <div class="d-flex justify-content-between mt-3" id="tracking-steps">
                    @foreach ($timelineSteps as $index => $step)
                        @php $isDone = $currentIndex !== false && $index <= $currentIndex; @endphp
                        <div class="text-center flex-grow-1">
                            <div class="mx-auto mb-2"
                                style="width:22px;height:22px;border-radius:50%;border:3px solid {{ $isDone ? '#7eff89ff' : '#d1d5db' }};background:{{ $isDone ? '#14f100ff' : '#fff' }};transition:all .3s ease;">
                            </div>
                            <div class="small {{ $isDone ? 'text-dark fw-700' : 'text-muted' }}" data-step="{{ $step['key'] }}">
                                {{ $step['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
                @if ($currentKey === 'in_transit' && !$trackingPayload)
                    <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
                        {{ translate('Tracking update will appear soon') }}
                    </div>
                @endif
            </div>

            <div class="row mt-3">
                <div class="col-md-4 mb-3">
                    <div class="p-3 rounded border h-100">
                        <div class="fw-700 text-dark">{{ translate('Shipment Details') }}</div>
                        <div class="text-muted small">{{ translate('Courier, AWB, and latest scan') }}</div>
                        <div class="mt-3 small">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ translate('Courier') }}</span>
                                <span id="tracking-courier" class="fw-700">{{ $order->shiprocket_courier_name ?? '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ translate('AWB / Tracking ID') }}</span>
                                <span id="tracking-awb" class="fw-700">{{ $order->shiprocket_awb ?? '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ translate('Last updated') }}</span>
                                <span id="tracking-updated" class="fw-700">
                                    {{ optional($order->status_updated_at)->format('d M Y h:i A') ?? ($lastActivity['date'] ?? '-') }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">{{ translate('Status code') }}</span>
                                <span id="tracking-code" class="fw-700">{{ $order->status_code ?? ($lastActivity['code'] ?? '-') }}</span>
                            </div>
                        </div>
                        @if ($order->getTrackingUrl())
                            <a href="{{ $order->getTrackingUrl() }}" target="_blank" class="btn btn-link px-0 mt-2">
                                {{ translate('Open carrier page') }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="p-3 rounded border h-100">
                        <div class="fw-700 text-dark">{{ translate('Shipping Address') }}</div>
                        <div class="text-muted small">{{ translate('Where your order will arrive') }}</div>
                        <div class="mt-3 small">
                            <div class="fw-700">{{ $shippingAddress->name ?? '-' }}</div>
                            <div class="text-muted">{{ $shippingAddress->phone ?? '' }}</div>
                            <div class="text-muted">
                                {{ $shippingAddress->address ?? '' }}, {{ $shippingAddress->city ?? '' }}
                                @if(isset($shippingAddress->state)), {{ $shippingAddress->state }} @endif
                                @if($shippingAddress->postal_code) - {{ $shippingAddress->postal_code }} @endif
                                @if($shippingAddress->country), {{ $shippingAddress->country }} @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="p-3 rounded border h-100">
                        <div class="fw-700 text-dark">{{ translate('Arrival') }}</div>
                        <div class="text-muted small">{{ translate('Estimated delivery window') }}</div>
                        <div class="mt-3">
                            <div class="fw-800 text-success" id="tracking-arrival">
                                {{ $etaText ?? translate('Arriving soon') }}
                            </div>
                            <div class="text-muted small" id="tracking-note">
                                {{ $lastActivity['activity'] ?? translate('We will update you when the courier shares the next scan.') }}
                            </div>
                        </div>
                        <button class="btn btn-primary btn-sm mt-3" id="refresh-tracking-btn">
                            {{ translate('Refresh Tracking') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    @php
        $prepaidDiscount = $order->prepaid_discount_amount ?? 0;
        $couponDiscount = $order->coupon_discount ?? 0;
        $totalSavings = $prepaidDiscount + $couponDiscount;
    @endphp
    <div class="card rounded-0 shadow-none border mb-4" style="background:#ffffff;">
        <div class="card-header border-bottom-0 pb-0">
            <div class="d-flex align-items-start justify-content-between flex-wrap">
                <div>
                    <h5 class="fs-18 fw-700 mb-1" style="color:#c70a0a;">{{ translate('Your Order Summary') }}</h5>
                    <p class="mb-2" style="color:#212121;">{{ translate('Review your purchased items, payment details, and order status') }}</p>
                </div>
                <!-- <div class="rounded-0 px-3 py-2 mb-2" style="background:#c70a0a; color:#fff;">
                    {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                </div> -->
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="p-3 h-100" style="border:1px solid #f0f0f0; border-radius:8px;">
                        <div class="d-flex align-items-center mb-3">
                            <i class="las la-box fs-24 mr-2" style="color:#c70a0a;"></i>
                            <div>
                                <div class="fw-700" style="color:#212121;">{{ translate('Order Information') }}</div>
                                <small class="text-muted">{{ translate('Quick snapshot of your order') }}</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-600" style="color:#212121;">{{ translate('Order Code') }}</span>
                            <span>{{ $order->code }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-600" style="color:#212121;">{{ translate('Order Date') }}</span>
                            <span>{{ date('d-m-Y H:i A', $order->date) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-600" style="color:#212121;">{{ translate('Payment Method') }}</span>
                            <span>{{ ucfirst(translate(str_replace('_', ' ', $order->payment_type))) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-600" style="color:#212121;">{{ translate('Payment Status') }}</span>
                            <span>{{ ucfirst(translate(str_replace('_', ' ', $order->payment_status))) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-600" style="color:#212121;">{{ translate('Shipping Method') }}</span>
                            <span>{{ translate('Flat shipping rate') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-600" style="color:#212121;">{{ translate('Delivery Status') }}</span>
                            <span class="fw-700" style="color:#c70a0a;">{{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}</span>
                        </div>
                        @if ($order->additional_info)
                            <div class="mt-3" style="color:#212121;">
                                <div class="fw-600 mb-1">{{ translate('Additional Info') }}</div>
                                <div class="text-muted">{{ $order->additional_info }}</div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="p-3 h-100" style="border:1px solid #f0f0f0; border-radius:8px;">
                        <div class="d-flex align-items-center mb-3">
                            <i class="las la-map-marker-alt fs-24 mr-2" style="color:#c70a0a;"></i>
                            <div>
                                <div class="fw-700" style="color:#212121;">{{ translate('Delivery Address') }}</div>
                                <small class="text-muted">{{ translate('Where your order will arrive') }}</small>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="fw-600" style="color:#212121;">{{ $shippingAddress->name ?? '-' }}</div>
                            @if ($order->user_id != null && $order->user->email)
                                <div class="text-muted">{{ $order->user->email }}</div>
                            @endif
                        </div>
                        <div class="text-muted" style="line-height:1.6;">
                            {{ $shippingAddress->address ?? '-' }}, {{ $shippingAddress->city ?? '' }}@if(isset($shippingAddress->state)), {{ $shippingAddress->state }} @endif @if($shippingAddress->postal_code) - {{ $shippingAddress->postal_code }} @endif @if($shippingAddress->country), {{ $shippingAddress->country }} @endif
                        </div>
                        @if ($order->tracking_code || $order->shiprocket_awb)
                            <hr>
                            @if ($order->tracking_code)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-600" style="color:#212121;">{{ translate('Tracking Code') }}</span>
                                    <span>{{ $order->tracking_code }}</span>
                                </div>
                            @endif
                            @if ($order->shiprocket_awb)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-600" style="color:#212121;">{{ translate('Shiprocket AWB') }}</span>
                                    <span>{{ $order->shiprocket_awb }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-600" style="color:#212121;">{{ translate('Shiprocket Order ID') }}</span>
                                    <span>{{ $order->shiprocket_order_id ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-600" style="color:#212121;">{{ translate('Shiprocket Shipment ID') }}</span>
                                    <span>{{ $order->shiprocket_shipment_id ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-600" style="color:#212121;">{{ translate('Shiprocket Status') }}</span>
                                    <span>{{ translate($order->shiprocket_status ?? 'N/A') }}</span>
                                </div>
                                @if ($order->shiprocket_label_url)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-600" style="color:#212121;">{{ translate('Track/Label') }}</span>
                                        <a href="{{ $order->shiprocket_label_url }}" target="_blank" class="text-decoration-underline" style="color:#c70a0a;">{{ translate('View') }}</a>
                                    </div>
                                @endif
                                <button class="btn btn-sm mt-2" id="refresh_shiprocket_status_customer" style="border:1px solid #c70a0a; color:#c70a0a; background:transparent;">
                                    {{ translate('Refresh Shiprocket Status') }}
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details -->
    <div class="row gutters-16">
        <div class="col-md-9">
            <div class="card rounded-0 shadow-none border mt-2 mb-4">
                <div class="card-header border-bottom-0">
                    <h5 class="fs-16 fw-700 text-dark mb-0">{{ translate('Order Details') }}</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="aiz-table table">
                        <thead class="text-gray fs-12">
                            <tr>
                                <th class="pl-0">#</th>
                                <th class="text-center" data-breakpoints="md">{{ translate('Image') }}</th>
                                <th width="30%">{{ translate('Product') }}</th>
                                <th data-breakpoints="md">{{ translate('Variation') }}</th>
                                <th>{{ translate('Quantity') }}</th>
                                <!-- <th data-breakpoints="md">{{ translate('Delivery Type') }}</th> -->
                                <th>{{ translate('Price') }}</th>
                                @if (addon_is_activated('refund_request'))
                                    <th data-breakpoints="md">{{ translate('Refund') }}</th>
                                @endif
                                <th data-breakpoints="md" class="text-right pr-0">{{ translate('Review') }}</th>
                            </tr>
                        </thead>
                        <tbody class="fs-14">
                            @foreach ($order->orderDetails as $key => $orderDetail)
                                <tr>
                                    <td class="pl-0">{{ sprintf('%02d', $key+1) }}</td>
                                    <td class="text-center">
                                        @php $thumb = $orderDetail->product->thumbnail_img ?? get_setting('default_product_img'); @endphp
                                        <a href="{{ $orderDetail->product ? route('product', $orderDetail->product->slug) : '#' }}" target="_blank">
                                            <img src="{{ uploaded_asset($thumb) }}" alt="{{ $orderDetail->product->getTranslation('name') ?? 'Product' }}"
                                                 class="img-fit size-60">
                                        </a>
                                    </td>
                                    <td>
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <a href="{{ route('product', $orderDetail->product->slug) }}"
                                                target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                                        @elseif($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <a href="{{ route('auction-product', $orderDetail->product->slug) }}"
                                                target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                                        @else
                                            <strong>{{ translate('Product Unavailable') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $orderDetail->variation }}
                                    </td>
                                    <td>
                                        {{ $orderDetail->quantity }}
                                    </td>
                                    <!-- <td>
                                        @if ($order->shipping_type != null && $order->shipping_type == 'home_delivery')
                                            {{ translate('Home Delivery') }}
                                        @elseif ($order->shipping_type == 'pickup_point')
                                            @if ($order->pickup_point != null)
                                                {{ $order->pickup_point->name }} ({{ translate('Pickip Point') }})
                                            @else
                                                {{ translate('Pickup Point') }}
                                            @endif
                                        @elseif($order->shipping_type == 'carrier')
                                            @if ($order->carrier != null)
                                                {{ $order->carrier->name }} ({{ translate('Carrier') }})
                                                <br>
                                                {{ translate('Transit Time').' - '.$order->carrier->transit_time }}
                                            @else
                                                {{ translate('Carrier') }}
                                            @endif
                                        @endif
                                    </td> -->
                                    <td class="fw-700">{{ single_price($orderDetail->price) }}</td>
                                    @if (addon_is_activated('refund_request'))
                                        @php
                                            $no_of_max_day = $orderDetail->refund_days;

                                            $last_refund_date = null;
                                            if ($order->delivered_date && $no_of_max_day > 0) {
                                                $last_refund_date = Carbon\Carbon::parse($order->delivered_date)->addDays($no_of_max_day);
                                            }
                                            
                                            $today_date = Carbon\Carbon::now();
                                            
                                        @endphp
                                        <td>
                                            @if (
                                                    $orderDetail->product != null &&
                                                    $orderDetail->refund_request == null &&
                                                    $last_refund_date &&
                                                    $today_date <= $last_refund_date &&
                                                    $order->payment_status == 'paid' &&
                                                    $order->delivery_status == 'delivered'
                                                )

                                                <a href="{{ route('refund_request_send_page', $orderDetail->id) }}"
                                                    class="btn btn-outline-dark btn-sm rounded-0">
                                                    {{ translate('Send') }}
                                                </a>
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 0)
                                                <b class="text-info">{{ translate('Pending') }}</b>
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 2)
                                                <b class="text-danger">{{ translate('Rejected') }}</b>
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 1)
                                                <b class="text-success">{{ translate('Approved') }}</b>
                                            @elseif ($orderDetail->product != null && $orderDetail->refund_days != 0)
                                                <b>{{ translate('N/A') }}</b>
                                            @else
                                                <b>{{ translate('Non-refundable') }}</b>
                                            @endif
                                        </td>
                                    @endif
                                        <td class="text-xl-right pr-0">
                                            @if ($orderDetail->delivery_status == 'delivered')
                                                <a href="javascript:void(0);" onclick="product_review('{{ $orderDetail->product_id }}', '{{ $order->id }}')"
                                                    class="btn btn-outline-dark btn-sm rounded-0"> {{ translate('Review') }} </a>
                                            @else
                                                <span class="text-danger">{{ translate('Not Delivered Yet') }}</span>
                                            @endif
                                        </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Order Amount (Amazon-style summary) -->
        <div class="col-md-3">
            <div class="card rounded-0 shadow-none border mt-2" style="background:#ffffff;">
                <div class="card-header border-bottom-0 pb-2">
                    <div class="d-flex flex-column">
                        <span class="fs-16 fw-700" style="color:#c70a0a;">{{ translate('Amount Details') }}</span>
                        <!-- <small style="color:#212121;">{{ translate('Review your purchased items, payment details, and order status') }}</small> -->
                    </div>
                </div>
                <div class="card-body pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-700" style="color:#212121;">{{ translate('Items Summary') }}</span>
                        <span class=" rounded-0 px-2" style="background:#212121; color:#fff;">{{ $order->orderDetails->sum('quantity') }} {{ translate('items') }}</span>
                    </div>
                    <!-- <div class="mb-3" style="border:1px solid #f0f0f0; border-radius:8px;">
                        @foreach ($order->orderDetails as $detail)
                            @php
                                $product = $detail->product;
                                $thumb = $product->thumbnail_img ?? get_setting('default_product_img');
                                $productName = $product ? $product->getTranslation('name') : translate('Product Unavailable');
                            @endphp
                            <div class="d-flex p-3 align-items-center">
                                <div class="mr-3" style="width:48px; height:48px;">
                                    <img src="{{ uploaded_asset($thumb) }}" alt="{{ $productName ?? 'Product' }}" class="img-fit rounded" style="width:48px; height:48px; object-fit:cover;">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-600" style="color:#212121;">{{ $productName }}</div>
                                    <div class="text-muted">{{ translate('Qty') }}: {{ $detail->quantity }} × {{ single_price($detail->price / max($detail->quantity,1)) }}</div>
                                </div>
                                <div class="fw-700" style="color:#212121;">{{ single_price($detail->price) }}</div>
                            </div>
                            @if(!$loop->last)
                                <div style="height:1px; background:#f4f4f4;"></div>
                            @endif
                        @endforeach
                    </div> -->
                    <div class="mb-2" style="color:#212121; font-weight:700;">{{ translate('Price Breakdown') }}</div>
                    <div class="d-flex justify-content-between mb-2" style="color:#212121;">
                        <span>{{ translate('Subtotal') }}</span>
                        <span>{{ single_price($order->orderDetails->sum('price')) }}</span>
                    </div>
                    @php
                        $ship_fee = $order->orderDetails->sum('shipping_cost');
                    @endphp
                    <div class="d-flex justify-content-between mb-2" style="color:#212121;">
                        <span>{{ translate('Shipping') }}</span>
                        <span class="{{ $ship_fee == 0 ? 'text-success' : '' }}">{{ $ship_fee == 0 ? translate('Free') : single_price($ship_fee) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" style="color:#212121;">
                        <span>{{ translate('Coupon Discount') }}</span>
                        <span class="text-success">- {{ single_price($couponDiscount) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" style="color:#212121;">
                        <span>{{ translate('Prepaid Discount') }}</span>
                        <span class="text-success">- {{ single_price($prepaidDiscount) }}</span>
                    </div>
                    @if ($order->orderDetails->sum('tax') > 0)
                        <div class="d-flex justify-content-between mb-2" style="color:#212121;">
                            <span>{{ translate('Tax') }}</span>
                            <span>{{ single_price($order->orderDetails->sum('tax')) }}</span>
                        </div>
                    @endif
                    <div style="height:1px; background:#f4f4f4; margin:12px 0;"></div>
                    <div class="d-flex justify-content-between align-items-center mb-2" style="color:#212121;">
                        <span class="fw-700">{{ translate('Total') }}</span>
                        <span class="fw-800" style="color:#c70a0a; font-size:18px;">{{ single_price($order->grand_total) }}</span>
                    </div>
                    <!-- <div class="mb-3" style="color:#16a34a; font-weight:700;">
                        {{ translate('You saved') }} {{ single_price($totalSavings) }} {{ translate('on this order') }} 🎉
                    </div>
                    <div class="mb-3" style="color:#6c757d; font-size:12px;">
                        <div>{{ translate('All prices include applicable taxes') }}</div>
                        <div>{{ translate('Invoice will be available after delivery') }}</div>
                    </div> -->
                </div>
            </div>
            @if ($order->payment_status == 'unpaid' && $order->delivery_status == 'pending' && $order->manual_payment == 0)
                <button
                    @if(addon_is_activated('offline_payment'))
                        onclick="select_payment_type({{ $order->id }})"
                    @else
                        onclick="online_payment({{ $order->id }})"
                    @endif
                    class="btn btn-block mt-2" style="background:#c70a0a; color:#fff;">
                    {{ translate('Make Payment') }}
                </button>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function() {
        var btn = document.getElementById('refresh_shiprocket_status_customer');
        if (!btn) return;
        btn.addEventListener('click', function() {
            btn.disabled = true;
            $.post('{{ route('shiprocket.track') }}', {
                _token: '{{ csrf_token() }}',
                order_id: {{ $order->id }}
            }, function(data) {
                AIZ.plugins.notify('success', data.message || "{{ translate('Shiprocket status updated') }}");
                location.reload();
            }).fail(function(xhr) {
                btn.disabled = false;
                var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "{{ translate('Failed to fetch Shiprocket status') }}";
                AIZ.plugins.notify('danger', message);
            });
        });
    })();
</script>
@endpush

@section('modal')
    <!-- Product Review Modal -->
    <div class="modal fade" id="product-review-modal">
        <div class="modal-dialog">
            <div class="modal-content" id="product-review-modal-content">

            </div>
        </div>
    </div>

    <!-- Select Payment Type Modal -->
    <div class="modal fade" id="payment_type_select_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('Select Payment Type') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="order_id" name="order_id" value="{{ $order->id }}">
                    <div class="row">
                        <div class="col-md-2">
                            <label>{{ translate('Payment Type') }}</label>
                        </div>
                        <div class="col-md-10">
                            <div class="mb-3">
                                <select class="form-control aiz-selectpicker rounded-0" onchange="payment_modal(this.value)"
                                    data-minimum-results-for-search="Infinity">
                                    <option value="">{{ translate('Select One') }}</option>
                                    <option value="online">{{ translate('Online payment') }}</option>
                                    <option value="offline">{{ translate('Offline payment') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-right">
                        <button type="button" class="btn btn-sm btn-primary rounded-0 transition-3d-hover mr-1"
                            id="payment_select_type_modal_cancel" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Online payment Modal -->
    <div class="modal fade" id="online_payment_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('Make Payment') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body gry-bg px-3 pt-3" style="overflow-y: inherit;">
                    <form class="" action="{{ route('order.re_payment') }}"
                        method="post">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Payment Method') }}</label>
                            </div>
                            <div class="col-md-10">
                            <div class="mb-3">
                                <select class="form-control selectpicker rounded-0" data-live-search="true" name="payment_option" required>
                                    @include('partials.online_payment_options')
                                    @if (get_setting('wallet_system') == 1 && (auth()->user()->balance >= $order->grand_total))
                                        <option value="wallet">{{ translate('Wallet') }}</option>
                                    @endif
                                </select>
                            </div>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <button type="button" class="btn btn-sm btn-secondary rounded-0 transition-3d-hover mr-1"
                                data-dismiss="modal">{{ translate('cancel') }}</button>
                            <button type="submit"
                                class="btn btn-sm btn-primary rounded-0 transition-3d-hover mr-1">{{ translate('Confirm') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- offline payment Modal -->
    <div class="modal fade" id="offline_order_re_payment_modal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('Offline Order Payment') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="offline_order_re_payment_modal_body"></div>
            </div>
        </div>
    </div>

@endsection


@section('script')
    <script type="text/javascript">
        (function () {
            const steps = ['ordered','shipped','in_transit','out_for_delivery','delivered'];
            const progressBar = document.getElementById('tracking-progress-bar');
            const etaEl = document.getElementById('tracking-eta');
            const arrivalEl = document.getElementById('tracking-arrival');
            const noteEl = document.getElementById('tracking-note');
            const courierEl = document.getElementById('tracking-courier');
            const awbEl = document.getElementById('tracking-awb');
            const updatedEl = document.getElementById('tracking-updated');
            const codeEl = document.getElementById('tracking-code');
            const refreshBtn = document.getElementById('refresh-tracking-btn');
            const statusBadge = document.querySelector('.badge.badge-primary');
            const stepsContainer = document.getElementById('tracking-steps');
            const legacyBtn = document.getElementById('refresh_shiprocket_status_customer');

            function setStep(status) {
                const idx = steps.indexOf(status);
                const progress = idx >= 0 ? (idx / (steps.length - 1)) * 100 : 40;
                if (progressBar) progressBar.style.width = progress + '%';
                if (stepsContainer) {
                    const nodes = stepsContainer.querySelectorAll('[data-step]');
                    nodes.forEach((el, i) => {
                        const isDone = idx >= 0 && i <= idx;
                        el.classList.toggle('text-dark', isDone);
                        el.classList.toggle('fw-700', isDone);
                        el.classList.toggle('text-muted', !isDone);
                        const dot = el.previousElementSibling;
                        if (dot) {
                            dot.style.borderColor = isDone ? '#0d6efd' : '#d1d5db';
                            dot.style.background = isDone ? '#0d6efd' : '#fff';
                        }
                    });
                }
            }

            function updateFields(data) {
                if (statusBadge) statusBadge.innerText = data.status_label || data.status;
                if (etaEl) etaEl.innerText = data.eta || "{{ translate('Tracking update will appear soon') }}";
                if (arrivalEl) arrivalEl.innerText = data.eta || "{{ translate('Arriving soon') }}";
                if (noteEl) noteEl.innerText = (data.last_activity && data.last_activity.activity) ? data.last_activity.activity : "{{ translate('We will update you when the courier shares the next scan.') }}";
                if (courierEl) courierEl.innerText = data.courier || '-';
                if (awbEl) awbEl.innerText = data.awb || '-';
                if (updatedEl) updatedEl.innerText = data.status_updated_at || (data.last_activity ? data.last_activity.date : '-') || '-';
                if (codeEl) codeEl.innerText = data.status_code || data.status || '-';
                setStep(data.status || 'in_transit');
            }

            function fetchTracking() {
                if (refreshBtn) refreshBtn.disabled = true;
                if (legacyBtn) legacyBtn.disabled = true;
                $.get('{{ route('order.track', $order->id) }}', {}, function (resp) {
                    updateFields(resp);
                }).fail(function () {
                    AIZ.plugins.notify('danger', "{{ translate('Failed to fetch tracking update') }}");
                }).always(function () {
                    if (refreshBtn) refreshBtn.disabled = false;
                    if (legacyBtn) legacyBtn.disabled = false;
                });
            }

            if (refreshBtn) {
                refreshBtn.addEventListener('click', fetchTracking);
            }
            if (legacyBtn) {
                legacyBtn.addEventListener('click', fetchTracking);
            }
            document.addEventListener('DOMContentLoaded', fetchTracking);
        })();

        function product_review(product_id,order_id) {
            $.post('{{ route('product_review_modal') }}', {
                _token: '{{ @csrf_token() }}',
                product_id: product_id,
                order_id: order_id
            }, function(data) {
                $('#product-review-modal-content').html(data);
                $('#product-review-modal').modal('show', {
                    backdrop: 'static'
                });
                AIZ.extra.inputRating();
            });
        }

        function select_payment_type(id) {
            $('#payment_type_select_modal').modal('show');
        }

        function payment_modal(type) {
            if (type == 'online') {
                $("#payment_select_type_modal_cancel").click();
                online_payment();
            } else if (type == 'offline') {
                $("#payment_select_type_modal_cancel").click();
                $.post('{{ route('offline_order_re_payment_modal') }}', {
                    _token: '{{ csrf_token() }}',
                    order_id: '{{ $order->id }}'
                }, function(data) {
                    $('#offline_order_re_payment_modal_body').html(data);
                    $('#offline_order_re_payment_modal').modal('show');
                });
            }
        }

        function online_payment() {
            $('input[name=customer_package_id]').val();
            $('#online_payment_modal').modal('show');
        }

    </script>
@endsection
