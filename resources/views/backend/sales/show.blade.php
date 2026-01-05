@extends('backend.layouts.app')

@section('content')

    <div class="card">
        <div class="card-header">
            <h1 class="h2 fs-16 mb-0">{{ translate('Order Details') }}</h1>
        </div>

      @php
                $timelineSteps = [
                    ['key' => 'ordered', 'label' => translate('Ordered')],
                    ['key' => 'shipped', 'label' => translate('Shipped')],
                    ['key' => 'in_transit', 'label' => translate('In-Transit')],
                    ['key' => 'out_for_delivery', 'label' => translate('Out for Delivery')],
                    ['key' => 'delivered', 'label' => translate('Delivered')],
                ];
                $trackingPayload = $order->shiprocket_tracking_payload ? json_decode($order->shiprocket_tracking_payload, true) : null;
                $currentKey = $order->trackingStatus();
                $currentIndex = collect($timelineSteps)->search(fn ($step) => $step['key'] === $currentKey);
                $etaText = $trackingPayload['eta'] ?? null;
                $lastActivity = $trackingPayload['last_activity'] ?? null;
            @endphp
            <div class=" mt-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                        <div>
                            <h5 class="mb-1">{{ translate('Shipment Timeline') }}</h5>
                            <div class="text-muted small">{{ $lastActivity['activity'] ?? translate('Auto-updated from Shiprocket') }}</div>
                        </div>
                        <div class="text-right">
                            <span class=" badge-primary px-3 py-2" id="admin-tracking-badge">{{ $order->trackingStatusLabel() }}</span>
                            @if ($etaText)
                                <div class="text-success small" id="admin-tracking-eta">{{ $etaText }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar bg-primary" role="progressbar" id="admin-tracking-bar"
                            style="width: {{ (($currentIndex !== false ? $currentIndex : 2) / max(count($timelineSteps)-1,1)) * 100 }}%;">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3" id="admin-tracking-steps">
                        @foreach ($timelineSteps as $index => $step)
                            @php $isDone = $currentIndex !== false && $index <= $currentIndex; @endphp
                            <div class="text-center flex-grow-1">
                                <div class="mx-auto mb-1"
                                     style="width:18px;height:18px;border-radius:50%;border:3px solid {{ $isDone ? 'rgb(150 255 167)' : '#d1d5db' }};background:{{ $isDone ? 'rgb(1 225 10)' : '#fff' }};">
                                </div>
                                <div class="small {{ $isDone ? 'text-dark font-weight-bold' : 'text-muted' }}" data-step="{{ $step['key'] }}">
                                    {{ $step['label'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="small text-muted">{{ translate('Courier') }}</div>
                            <div class="fw-700" id="admin-tracking-courier">{{ $order->shiprocket_courier_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">{{ translate('AWB') }}</div>
                            <div class="fw-700" id="admin-tracking-awb">{{ $order->shiprocket_awb ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">{{ translate('Last Updated') }}</div>
                            <div class="fw-700" id="admin-tracking-updated">
                                {{ optional($order->status_updated_at)->format('d M Y h:i A') ?? ($lastActivity['date'] ?? '-') }}
                            </div>
                        </div>
                    </div>
                    @if (!empty($trackingPayload['raw']['shipment_track_activities']))
                        <div class="mt-3">
                            <div class="small text-muted mb-2">{{ translate('Status Log') }}</div>
                            <ul class="list-unstyled mb-0" id="admin-tracking-history">
                                @foreach ($trackingPayload['raw']['shipment_track_activities'] as $activity)
                                    <li class="d-flex justify-content-between border-bottom py-1 small">
                                        <span>{{ $activity['activity'] ?? '-' }}</span>
                                        <span class="text-muted">{{ $activity['date'] ?? '' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
            


        <div class="card-body">
            <div class="row gutters-5">
                <div class="col text-md-left text-center">
                </div>
                @php
                    $delivery_status = $order->delivery_status;
                    $payment_status = $order->payment_status;
                    $admin_user_id = get_admin()->id;
                @endphp
                @if ($order->seller_id == $admin_user_id || get_setting('product_manage_by_admin') == 1)

                    <!--Assign Delivery Boy-->
                    @if (addon_is_activated('delivery_boy'))
                        <div class="col-md-3 ml-auto">
                            <label for="assign_deliver_boy">{{ translate('Assign Deliver Boy') }}</label>
                            @if (($delivery_status == 'pending' || $delivery_status == 'confirmed' || $delivery_status == 'picked_up') && auth()->user()->can('assign_delivery_boy_for_orders'))
                                <select class="form-control aiz-selectpicker" data-live-search="true"
                                    data-minimum-results-for-search="Infinity" id="assign_deliver_boy">
                                    <option value="">{{ translate('Select Delivery Boy') }}</option>
                                    @foreach ($delivery_boys as $delivery_boy)
                                        <option value="{{ $delivery_boy->id }}"
                                            @if ($order->assign_delivery_boy == $delivery_boy->id) selected @endif>
                                            {{ $delivery_boy->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" value="{{ optional($order->delivery_boy)->name }}"
                                    disabled>
                            @endif
                        </div>
                    @endif

                    <div class="col-md-3 ml-auto">
                        <label for="update_payment_status">{{ translate('Payment Status') }}</label>
                        @if (auth()->user()->can('update_order_payment_status') && $payment_status == 'unpaid')
                            {{-- <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity" id="update_payment_status"> --}}
                            <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity" id="update_payment_status" onchange="confirm_payment_status()">
                                <option value="unpaid" @if ($payment_status == 'unpaid') selected @endif>
                                    {{ translate('Unpaid') }}
                                </option>
                                <option value="paid" @if ($payment_status == 'paid') selected @endif>
                                    {{ translate('Paid') }}
                                </option>
                            </select>
                        @else
                            <input type="text" class="form-control" value="{{ ucfirst($payment_status) }}" disabled>
                        @endif
                    </div>
                    <div class="col-md-3 ml-auto">
                        <label for="update_delivery_status">{{ translate('Delivery Status') }}</label>
                        @if (auth()->user()->can('update_order_delivery_status') && $delivery_status != 'delivered' && $delivery_status != 'cancelled')
                            <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                                id="update_delivery_status">
                                <option value="pending" @if ($delivery_status == 'pending') selected @endif>
                                    {{ translate('Pending') }}
                                </option>
                                <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>
                                    {{ translate('Confirmed') }}
                                </option>
                                <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>
                                    {{ translate('Picked Up') }}
                                </option>
                                <option value="on_the_way" @if ($delivery_status == 'on_the_way') selected @endif>
                                    {{ translate('On The Way') }}
                                </option>
                                <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>
                                    {{ translate('Delivered') }}
                                </option>
                                <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>
                                    {{ translate('Cancel') }}
                                </option>
                            </select>
                        @else
                            <input type="text" class="form-control" value="{{ $delivery_status }}" disabled>
                        @endif
                    </div>
                    <div class="col-md-3 ml-auto">
                        <label for="update_tracking_code">
                            {{ translate('Tracking Code (optional)') }}
                        </label>
                        <input type="text" class="form-control" id="update_tracking_code"
                            value="{{ $order->tracking_code }}">
                    </div>
                    @php
                        $shiprocketTracking = $order->shiprocket_tracking_payload ? json_decode($order->shiprocket_tracking_payload, true) : null;
                    @endphp
                    <div class="col-md-3 ml-auto">
                        <label>{{ translate('Shiprocket') }}</label>
                        @if ($order->shiprocket_shipment_id)
                            <div class="card border shadow-none">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-inline badge-success text-capitalize">
                                            {{ translate($order->shiprocket_status ?? 'created') }}
                                        </span>
                                        @if ($order->shiprocket_awb)
                                            <span class="text-muted">
                                                {{ translate('AWB') }}:
                                                @if(!empty($shiprocketTracking['track_url']))
                                                    <a href="{{ $shiprocketTracking['track_url'] }}" target="_blank">{{ $order->shiprocket_awb }}</a>
                                                @else
                                                    {{ $order->shiprocket_awb }}
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mt-2">
                                        <div>{{ translate('Shiprocket Order ID') }}: {{ $order->shiprocket_order_id ?? '-' }}</div>
                                        <div>{{ translate('Shiprocket Shipment ID') }}: {{ $order->shiprocket_shipment_id ?? '-' }}</div>
                                        @if ($order->shiprocket_courier_name)
                                            <div>{{ translate('Courier') }}: {{ $order->shiprocket_courier_name }}</div>
                                        @endif
                                        @if ($order->getTrackingUrl())
                                            <div class="mt-1">
                                                <a href="{{ $order->getTrackingUrl() }}" target="_blank">{{ translate('Tracking URL') }}</a>
                                            </div>
                                        @endif
                                        @if (!empty($shiprocketTracking['last_activity']))
                                            <div class="mt-1">
                                                <strong>{{ translate('Last Update') }}:</strong>
                                                <div class="text-dark">
                                                    {{ $shiprocketTracking['last_activity']['activity'] ?? '' }}
                                                </div>
                                                <div class="text-muted">
                                                    {{ $shiprocketTracking['last_activity']['date'] ?? '' }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    @if ($order->shiprocket_label_url)
                                        <a href="{{ $order->shiprocket_label_url }}" target="_blank" class="d-block small mt-2">
                                            {{ translate('Label/Tracking') }}
                                        </a>
                                    @endif
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button class="btn btn-soft-primary btn-sm mr-1 mb-2" id="refresh_shiprocket_status">
                                            {{ translate('Refresh Status') }}
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm mr-1 mb-2" id="generate_shiprocket_label">
                                            {{ translate('Generate Label') }}
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm mr-1 mb-2" id="generate_shiprocket_invoice">
                                            {{ translate('Generate Invoice') }}
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm mb-2" id="cancel_shiprocket_shipment">
                                            {{ translate('Cancel Shipment') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="card border shadow-none">
                                <div class="card-body p-3">
                                    <div class="small text-muted mb-2">
                                        {{ translate('No Shiprocket shipment yet.') }}
                                    </div>
                                    <button class="btn btn-primary w-100" id="push_to_shiprocket">
                                        {{ translate('Push to Shiprocket') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

          
            <div class="row gutters-5">
                <div class="col text-md-left text-center">
                    @if(json_decode($order->shipping_address))
                        <address>
                            <strong class="text-main">
                                {{ json_decode($order->shipping_address)->name }}
                            </strong><br>
                            {{ json_decode($order->shipping_address)->email }}<br>
                            {{ json_decode($order->shipping_address)->phone }}<br>
                            {{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, @if(isset(json_decode($order->shipping_address)->state)) {{ json_decode($order->shipping_address)->state }} - @endif {{ json_decode($order->shipping_address)->postal_code }}<br>
                            {{ json_decode($order->shipping_address)->country }}
                        </address>
                    @else
                        <address>
                            <strong class="text-main">
                                {{ $order->user->name }}
                            </strong><br>
                            {{ $order->user->email }}<br>
                            {{ $order->user->phone }}<br>
                        </address>
                    @endif
                    @if ($order->manual_payment && is_array(json_decode($order->manual_payment_data, true)))
                        <br>
                        <strong class="text-main">{{ translate('Payment Information') }}</strong><br>
                        {{ translate('Name') }}: {{ json_decode($order->manual_payment_data)->name }},
                        {{ translate('Amount') }}:
                        {{ single_price(json_decode($order->manual_payment_data)->amount) }},
                        {{ translate('TRX ID') }}: {{ json_decode($order->manual_payment_data)->trx_id }}
                        <br>
                        <a href="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" target="_blank">
                            <img src="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" alt=""
                                height="100">
                        </a>
                    @endif
                </div>
                <div class="col-md-4">
                    <table class="ml-auto">
                        <tbody>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Order #') }}</td>
                                <td class="text-info text-bold text-right"> {{ $order->code }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Order Status') }}</td>
                                <td class="text-right">
                                    @if ($delivery_status == 'delivered')
                                        <span class="badge badge-inline badge-success">
                                            {{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}
                                        </span>
                                    @else
                                        <span class="badge badge-inline badge-info">
                                            {{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Order Date') }} </td>
                                <td class="text-right">{{ date('d-m-Y h:i A', $order->date) }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">
                                    {{ translate('Total amount') }}
                                </td>
                                <td class="text-right">
                                    {{ single_price($order->grand_total) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Payment method') }}</td>
                                <td class="text-right">
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Additional Info') }}</td>
                                <td class="text-right">{{ $order->additional_info }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr class="new-section-sm bord-no">
            <div class="row">
                <div class="col-lg-12 table-responsive">
                    <table class="table-bordered aiz-table invoice-summary table">
                        <thead>
                            <tr class="bg-trans-dark">
                                <th data-breakpoints="lg" class="min-col">#</th>
                                <th width="10%">{{ translate('Photo') }}</th>
                                <th class="text-uppercase">{{ translate('Description') }}</th>
                                <th data-breakpoints="lg" class="text-uppercase">{{ translate('Delivery Type') }}</th>
                                <th data-breakpoints="lg" class="min-col text-uppercase text-center">
                                    {{ translate('Qty') }}
                                </th>
                                <th data-breakpoints="lg" class="min-col text-uppercase text-center">
                                    {{ translate('Price') }}</th>
                                <th data-breakpoints="lg" class="min-col text-uppercase text-right">
                                    {{ translate('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderDetails as $key => $orderDetail)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank">
                                                <img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}">
                                            </a>
                                        @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank">
                                                <img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}">
                                            </a>
                                        @else
                                            <strong>{{ translate('N/A') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <strong>
                                                <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank"
                                                    class="text-muted">
                                                    {{ $orderDetail->product->getTranslation('name') }}
                                                </a>
                                            </strong>
                                            <small>
                                                {{ $orderDetail->variation }}
                                            </small>
                                            <br>
                                            <small>
                                                @php
                                                    $product_stock = $orderDetail->product->stocks->where('variant', $orderDetail->variation)->first();
                                                @endphp
                                                {{translate('SKU')}}: {{ $product_stock['sku'] ?? '' }}
                                            </small>
                                        @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <strong>
                                                <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"
                                                    class="text-muted">
                                                    {{ $orderDetail->product->getTranslation('name') }}
                                                </a>
                                            </strong>
                                        @else
                                            <strong>{{ translate('Product Unavailable') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($order->shipping_type != null && $order->shipping_type == 'home_delivery')
                                            {{ translate('Home Delivery') }}
                                        @elseif ($order->shipping_type == 'pickup_point')
                                            @if ($order->pickup_point != null)
                                                {{ $order->pickup_point->getTranslation('name') }}
                                                ({{ translate('Pickup Point') }})
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
                                    </td>
                                    <td class="text-center">
                                        {{ $orderDetail->quantity }}
                                    </td>
                                    <td class="text-center">
                                        {{ single_price($orderDetail->price / $orderDetail->quantity) }}
                                    </td>
                                    <td class="text-center">
                                        {{ single_price($orderDetail->price) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="clearfix float-right">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>
                                <strong class="text-muted">{{ translate('Sub Total') }} :</strong>
                            </td>
                            <td>
                                {{ single_price($order->orderDetails->sum('price')) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">{{ translate('Tax') }} :</strong>
                            </td>
                            <td>
                                {{ single_price($order->orderDetails->sum('tax')) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">{{ translate('Shipping') }} :</strong>
                            </td>
                            <td>
                                {{ single_price($order->orderDetails->sum('shipping_cost')) }}
                            </td>
                        </tr>
                        @if($order->prepaid_discount_amount > 0)
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('Prepaid Discount') }} :</strong>
                                </td>
                                <td class="text-success">
                                    -{{ single_price($order->prepaid_discount_amount) }}
                                </td>
                            </tr>
                        @endif
                        @php
                            $totalDiscounts = ($order->prepaid_discount_amount ?? 0) + ($order->coupon_discount ?? 0);
                        @endphp
                        <tr>
                            <td>
                                <strong class="text-muted">{{ translate('Coupon') }} :</strong>
                            </td>
                            <td>
                                {{ single_price($order->coupon_discount) }}
                            </td>
                        </tr>
                        @if($totalDiscounts > 0)
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('Total Discounts') }} :</strong>
                                </td>
                                <td class="text-success">
                                    -{{ single_price($totalDiscounts) }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td>
                                <strong class="text-muted">{{ translate('TOTAL') }} :</strong>
                            </td>
                            <td class="text-muted h5">
                                {{ single_price($order->grand_total) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="no-print text-right">
                    <a href="{{ route('invoice.download', $order->id) }}" type="button" class="btn btn-icon btn-light"><i
                            class="las la-print"></i></a>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('modal')

    <!-- confirm payment Status Modal -->
    <div id="confirm-payment-status" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 540px;">
            <div class="modal-content p-2rem">
                <div class="modal-body text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="64" viewBox="0 0 72 64">
                        <g id="Octicons" transform="translate(-0.14 -1.02)">
                          <g id="alert" transform="translate(0.14 1.02)">
                            <path id="Shape" d="M40.159,3.309a4.623,4.623,0,0,0-7.981,0L.759,58.153a4.54,4.54,0,0,0,0,4.578A4.718,4.718,0,0,0,4.75,65.02H67.587a4.476,4.476,0,0,0,3.945-2.289,4.773,4.773,0,0,0,.046-4.578Zm.6,52.555H31.582V46.708h9.173Zm0-13.734H31.582V23.818h9.173Z" transform="translate(-0.14 -1.02)" fill="#ffc700" fill-rule="evenodd"/>
                          </g>
                        </g>
                    </svg>
                    <p class="mt-3 mb-3 fs-16 fw-700">{{translate('Are you sure you want to change the payment status?')}}</p>
                    <button type="button" class="btn btn-light rounded-2 mt-2 fs-13 fw-700 w-150px" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="button" onclick="update_payment_status()" class="btn btn-success rounded-2 mt-2 fs-13 fw-700 w-150px">{{translate('Confirm')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script type="text/javascript">
        $('#assign_deliver_boy').on('change', function() {
            var order_id = {{ $order->id }};
            var delivery_boy = $('#assign_deliver_boy').val();
            $.post('{{ route('orders.delivery-boy-assign') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                delivery_boy: delivery_boy
            }, function(data) {
                AIZ.plugins.notify('success', '{{ translate('Delivery boy has been assigned') }}');
            });
        });
        $('#update_delivery_status').on('change', function() {
            var order_id = {{ $order->id }};
            var status = $('#update_delivery_status').val();
            $.post('{{ route('orders.update_delivery_status') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                status: status
            }, function(data) {
                AIZ.plugins.notify('success', '{{ translate('Delivery status has been updated') }}');
                location.reload();
            });
        });

        // Payment Status Update
        function confirm_payment_status(value){
            $('#confirm-payment-status').modal('show');
        }

        function update_payment_status(){
            $('#confirm-payment-status').modal('hide');
            var order_id = {{ $order->id }};
            $.post('{{ route('orders.update_payment_status') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                status: 'paid'
            }, function(data) {
                $('#update_payment_status').prop('disabled', true);
                AIZ.plugins.bootstrapSelect('refresh');
                AIZ.plugins.notify('success', '{{ translate('Payment status has been updated') }}');
                location.reload();
            });
        }

        $('#update_tracking_code').on('change', function() {
            var order_id = {{ $order->id }};
            var tracking_code = $('#update_tracking_code').val();
            $.post('{{ route('orders.update_tracking_code') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                tracking_code: tracking_code
            }, function(data) {
                AIZ.plugins.notify('success', '{{ translate('Order tracking code has been updated') }}');
            });
        });

        $('#push_to_shiprocket').on('click', function() {
            var button = $(this);
            button.prop('disabled', true);
            $.post('{{ route('orders.push_to_shiprocket') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: {{ $order->id }}
            }, function(data) {
                AIZ.plugins.notify('success', data.message ?? "{{ translate('Pushed to Shiprocket') }}");
                location.reload();
            }).fail(function(xhr) {
                button.prop('disabled', false);
                var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "{{ translate('Failed to push to Shiprocket') }}";
                AIZ.plugins.notify('danger', message);
            });
        });

        $('#refresh_shiprocket_status').on('click', function() {
            var button = $(this);
            button.prop('disabled', true);
            fetchAdminTracking().always(function () {
                button.prop('disabled', false);
            });
        });

        $('#generate_shiprocket_label').on('click', function() {
            var button = $(this);
            button.prop('disabled', true);
            $.post('{{ route('shiprocket.generate_label', $order->id) }}', { _token: '{{ @csrf_token() }}' })
                .done(function(resp) {
                    AIZ.plugins.notify('success', resp.message || "{{ translate('Label generated') }}");
                    if (resp.url) {
                        window.open(resp.url, '_blank');
                    }
                })
                .fail(function(xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "{{ translate('Failed to generate label') }}";
                    AIZ.plugins.notify('danger', message);
                })
                .always(function() { button.prop('disabled', false); });
        });

        $('#generate_shiprocket_invoice').on('click', function() {
            var button = $(this);
            button.prop('disabled', true);
            $.post('{{ route('shiprocket.generate_invoice', $order->id) }}', { _token: '{{ @csrf_token() }}' })
                .done(function(resp) {
                    AIZ.plugins.notify('success', resp.message || "{{ translate('Invoice generated') }}");
                    if (resp.url) {
                        window.open(resp.url, '_blank');
                    }
                })
                .fail(function(xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "{{ translate('Failed to generate invoice') }}";
                    AIZ.plugins.notify('danger', message);
                })
                .always(function() { button.prop('disabled', false); });
        });

        $('#cancel_shiprocket_shipment').on('click', function() {
            var button = $(this);
            if (!confirm("{{ translate('Are you sure you want to cancel this shipment?') }}")) {
                return;
            }
            button.prop('disabled', true);
            $.post('{{ route('shiprocket.cancel_shipment', $order->id) }}', { _token: '{{ @csrf_token() }}' })
                .done(function(resp) {
                    AIZ.plugins.notify('success', resp.message || "{{ translate('Shipment cancelled') }}");
                    location.reload();
                })
                .fail(function(xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "{{ translate('Failed to cancel shipment') }}";
                    AIZ.plugins.notify('danger', message);
                })
                .always(function() { button.prop('disabled', false); });
        });

        // Admin tracking timeline updater
        function fetchAdminTracking() {
            return $.get('{{ route('order.track', $order->id) }}', {}, function(resp) {
                var steps = ['ordered','shipped','in_transit','out_for_delivery','delivered'];
                var idx = steps.indexOf(resp.status);
                var percent = idx >= 0 ? (idx / (steps.length - 1)) * 100 : 40;
                $('#admin-tracking-bar').css('width', percent + '%');
                $('#admin-tracking-badge').text(resp.status_label || resp.status);
                $('#admin-tracking-eta').text(resp.eta || '');
                $('#admin-tracking-courier').text(resp.courier || '-');
                $('#admin-tracking-awb').text(resp.awb || '-');
                $('#admin-tracking-updated').text(resp.status_updated_at || (resp.last_activity ? resp.last_activity.date : '-'));
                $('#admin-tracking-steps [data-step]').each(function(i, el){
                    var isDone = idx >=0 && i <= idx;
                    $(el).toggleClass('text-muted', !isDone);
                    $(el).toggleClass('text-dark font-weight-bold', isDone);
                    var dot = $(el).prev();
                    dot.css({
                        borderColor: isDone ? '#0d6efd' : '#d1d5db',
                        background: isDone ? '#0d6efd' : '#fff'
                    });
                });
                if (resp.history) {
                    var list = $('#admin-tracking-history');
                    if (list.length) {
                        list.empty();
                        resp.history.forEach(function (item) {
                            list.append('<li class="d-flex justify-content-between border-bottom py-1 small"><span>' +
                                (item.activity || '-') + '</span><span class="text-muted">' + (item.date || '') + '</span></li>');
                        });
                    }
                }
            }).fail(function() {
                AIZ.plugins.notify('danger', "{{ translate('Failed to fetch tracking update') }}");
            });
        }

        $(document).ready(function () {
            fetchAdminTracking();
        });
    </script>
@endsection
