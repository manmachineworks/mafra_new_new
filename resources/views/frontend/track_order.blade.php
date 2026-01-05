@extends('frontend.layouts.app')

@section('content')
    <section class="pt-4 mb-4">
        <div class="container text-center">
            <div class="row">
                <div class="col-lg-6 text-center text-lg-left">
                    <h1 class="fw-700 fs-20 fs-md-24 text-dark">{{ translate('Track Order') }}</h1>
                </div>
                <div class="col-lg-6">
                    <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                        <li class="breadcrumb-item has-transition opacity-50 hov-opacity-100">
                            <a class="text-reset" href="{{ route('home') }}">{{ translate('Home') }}</a>
                        </li>
                        <li class="text-dark fw-600 breadcrumb-item">
                            "{{ translate('Track Order') }}"
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <section class="mb-5">
        <div class="container text-left">
            <div class="row">
                <div class="col-xxl-5 col-xl-6 col-lg-8 mx-auto">
                    <form class="" action="{{ route('orders.track') }}" method="GET" enctype="multipart/form-data">
                        <div class="bg-white border rounded-0">
                            <div class="fs-15 fw-600 p-3 border-bottom text-center">
                                {{ translate('Check Your Order Status')}}
                            </div>
                            <div class="form-box-content p-3">
                                <div class="form-group">
                                    <input type="text" class="form-control rounded-0 mb-3" placeholder="{{ translate('Order Code')}}" name="order_code" required>
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary rounded-0 w-150px">{{ translate('Track Order')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @isset($order)
                <div class="bg-white border rounded-0 mt-5">
                    <div class="fs-15 fw-600 p-3">
                        {{ translate('Order Summary')}}
                    </div>
                    <div class="p-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Order Code')}}:</td>
                                        <td>{{ $order->code }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Customer')}}:</td>
                                        <td>{{ json_decode($order->shipping_address)->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Email')}}:</td>
                                        @if ($order->user_id != null)
                                            <td>{{ $order->user->email }}</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Shipping address')}}:</td>
                                        <td>{{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ json_decode($order->shipping_address)->country }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-lg-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Order date')}}:</td>
                                        <td>{{ date('d-m-Y H:i A', $order->date) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Total order amount')}}:</td>
                                        <!-- <td>{{ single_price($order->orderDetails->sum('price') + $order->orderDetails->sum('tax')) }}</td> -->
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Shipping method')}}:</td>
                                        <td>{{ translate('Flat shipping rate')}}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Payment method')}}:</td>
                                        <td>{{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Delivery Status')}}:</td>
                                        <td>{{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}</td>
                                    </tr>
                                    @if ($order->tracking_code)
                                        <tr>
                                            <td class="w-50 fw-600">{{ translate('Tracking code')}}:</td>
                                            <td>{{ $order->tracking_code }}</td>
                                        </tr>
                                    @endif
                                    @if ($order->shiprocket_awb)
                                        <tr>
                                            <td class="w-50 fw-600">{{ translate('Shiprocket AWB')}}:</td>
                                            <td>{{ $order->shiprocket_awb }}</td>
                                        </tr>
                                        <tr>
                                            <td class="w-50 fw-600">{{ translate('Shiprocket Order ID')}}:</td>
                                            <td>{{ $order->shiprocket_order_id ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="w-50 fw-600">{{ translate('Shiprocket Shipment ID')}}:</td>
                                            <td>{{ $order->shiprocket_shipment_id ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="w-50 fw-600">{{ translate('Shiprocket Status')}}:</td>
                                            <td>{{ translate($order->shiprocket_status ?? 'N/A') }}</td>
                                        </tr>
                                        @if ($order->shiprocket_label_url)
                                            <tr>
                                                <td class="w-50 fw-600">{{ translate('Track/Label')}}:</td>
                                                <td><a href="{{ $order->shiprocket_label_url }}" target="_blank">{{ translate('View') }}</a></td>
                                            </tr>
                                        @endif
                                    @endif
                                </table>
                                @if ($order->shiprocket_awb)
                                    <button class="btn btn-outline-primary btn-sm mt-2" id="refresh_shiprocket_status_track">
                                        {{ translate('Refresh Shiprocket Status') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>


                @foreach ($order->orderDetails as $key => $orderDetail)
                    @php
                        $status = $order->delivery_status;
                    @endphp
                    <div class="bg-white border rounded-0 mt-4">
                        
                        @if($orderDetail->product != null)
                        <div class="p-3">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="border-0">{{ translate('Product Name')}}</th>
                                        <th class="border-0">{{ translate('Quantity')}}</th>
                                        <th class="border-0">{{ translate('Shipped By')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                    <td>{{ $orderDetail->product->getTranslation('name') }} ({{ $orderDetail->variation }})</td>
                                        <td>{{ $orderDetail->quantity }}</td>
                                        <td>{{ $orderDetail->product->user->name }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                @endforeach

            @endisset
        </div>
    </section>
@endsection

@push('scripts')
<script>
    (function() {
        var btn = document.getElementById('refresh_shiprocket_status_track');
        if (!btn) return;
        btn.addEventListener('click', function() {
            btn.disabled = true;
            $.post('{{ route('shiprocket.track') }}', {
                _token: '{{ csrf_token() }}',
                order_code: '{{ isset($order) ? $order->code : '' }}'
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
