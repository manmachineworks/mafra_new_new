@extends('frontend.layouts.app')

@section('content')
    <style>
        :root{
            --brand:#c70a04;
            --dark:#212121;
            --white:#ffffff;
            --muted:#6b7280;
            --border:#e9ecef;
            --shadow:0 10px 30px rgba(33,33,33,.08);
            --radius:14px;
        }

        /* Page */
        .checkout-wrap{ padding: 12px 0 40px; }
        .checkout-title{ color: var(--dark); font-weight: 800; letter-spacing: .2px; }
        .checkout-subtitle{ color: var(--muted); font-size: 13px; }

        /* Cards */
        .co-card{
            background: var(--white);
            border: 1px solid var(--border) !important;
            border-radius: var(--radius) !important;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .co-card + .co-card{ margin-top: 16px; }

        /* Accordion header */
        .co-head{
            background: var(--white);
            padding: 16px 18px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            cursor:pointer;
            border-bottom: 1px solid var(--border);
        }
        .co-head-left{ display:flex; align-items:center; gap:10px; min-width:0; }
        .co-step-dot{
            width: 34px; height: 34px;
            border-radius: 999px;
            display:flex; align-items:center; justify-content:center;
            background: rgba(199,10,4,.08);
            color: var(--brand);
            font-weight: 800;
            flex:0 0 auto;
        }
        .co-head-title{
            color: var(--dark);
            font-weight: 800;
            font-size: 16px;
            line-height: 1.2;
            margin: 0;
        }
        .co-head-desc{
            color: var(--muted);
            font-size: 12px;
            margin: 2px 0 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 520px;
        }
        .co-chevron{
            color: var(--muted);
            font-size: 20px;
            transition: .2s ease;
        }
        .collapsed .co-chevron{ transform: rotate(-90deg); }
        .co-body{ padding: 16px 18px; background:#fff; }

        /* Buttons */
        .btn-brand{
            background: var(--brand) !important;
            border-color: var(--brand) !important;
            color: #fff !important;
            font-weight: 800;
            border-radius: 12px !important;
            padding: 12px 18px !important;
            box-shadow: 0 10px 20px rgba(199,10,4,.18);
        }
        .btn-brand:hover{ filter: brightness(.96); }
        .btn-link-brand{
            color: var(--dark) !important;
            font-weight: 800;
            text-decoration: none !important;
        }
        .btn-link-brand:hover{ color: var(--brand) !important; }

        /* Summary */
        .summary-sticky{ position: sticky; top: 92px; }
        .summary-offer{
            border: 1px dashed rgba(199,10,4,.35);
            background: rgba(199,10,4,.05);
            border-radius: var(--radius);
            padding: 14px 14px;
        }
        .summary-offer .title{ color: var(--dark); font-weight: 900; font-size: 14px; margin-bottom: 2px; }
        .summary-offer .desc{ color: var(--muted); font-size: 12px; line-height: 1.35; }

        /* Agree box */
        .agree-wrap{
            border-top: 1px solid var(--border);
            margin-top: 14px;
            padding-top: 14px;
            color: var(--muted);
            font-size: 13px;
        }
        .agree-wrap a{ color: var(--dark); font-weight: 800; }
        .agree-wrap a:hover{ color: var(--brand); }

        @media(max-width: 991px){
            .summary-sticky{ position: static; top:auto; }
            .co-head-desc{ max-width: 220px; }
        }
    </style>

    <section class="gry-bg checkout-wrap">
        <div class="container">

            <div class="mb-3">
                <div class="checkout-title fs-24">{{ translate('Checkout') }}</div>
                <div class="checkout-subtitle">
                    {{ translate('Secure checkout • Best offers auto-applied • Pay prepaid to save more') }}
                </div>
            </div>

            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-8 mx-auto">

                    <form class="form-default" data-toggle="validator"
                          action="{{ route('payment.checkout') }}"
                          role="form" method="POST" id="checkout-form">
                        @csrf

                        <div class="accordion" id="accordioncCheckoutInfo">

                            <!-- Shipping Info -->
                            <div class="card co-card">
                                <div class="co-head" id="headingShippingInfo"
                                     type="button" data-toggle="collapse"
                                     data-target="#collapseShippingInfo"
                                     aria-expanded="true" aria-controls="collapseShippingInfo">

                                    <div class="co-head-left">
                                        <div class="co-step-dot">1</div>
                                        <div class="min-width-0">
                                            <div class="co-head-title">{{ translate('Shipping Info') }}</div>
                                            <div class="co-head-desc">{{ translate('Choose address / fill shipping details') }}</div>
                                        </div>
                                    </div>

                                    <i class="las la-angle-down co-chevron"></i>
                                </div>

                                <div id="collapseShippingInfo" class="collapse show"
                                     aria-labelledby="headingShippingInfo"
                                     data-parent="#accordioncCheckoutInfo">
                                    <div class="co-body" id="shipping_info">
                                        @include('frontend.partials.cart.shipping_info', ['address_id' => $address_id])
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Info -->
                            <div class="card co-card">
                                <div class="co-head" id="headingDeliveryInfo"
                                     type="button" data-toggle="collapse"
                                     data-target="#collapseDeliveryInfo"
                                     aria-expanded="true" aria-controls="collapseDeliveryInfo">

                                    <div class="co-head-left">
                                        <div class="co-step-dot">2</div>
                                        <div class="min-width-0">
                                            <div class="co-head-title">{{ translate('Delivery Info') }}</div>
                                            <div class="co-head-desc">{{ translate('Select shipping method / pickup point') }}</div>
                                        </div>
                                    </div>

                                    <i class="las la-angle-down co-chevron"></i>
                                </div>

                                <div id="collapseDeliveryInfo" class="collapse show"
                                     aria-labelledby="headingDeliveryInfo"
                                     data-parent="#accordioncCheckoutInfo">
                                    <div class="co-body" id="delivery_info">
                                        @include('frontend.partials.cart.delivery_info', [
                                            'carts' => $carts,
                                            'carrier_list' => $carrier_list,
                                            'shipping_info' => $shipping_info
                                        ])
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Info -->
                            <div class="card co-card">
                                <div class="co-head" id="headingPaymentInfo"
                                     type="button" data-toggle="collapse"
                                     data-target="#collapsePaymentInfo"
                                     aria-expanded="true" aria-controls="collapsePaymentInfo">

                                    <div class="co-head-left">
                                        <div class="co-step-dot">3</div>
                                        <div class="min-width-0">
                                            <div class="co-head-title">{{ translate('Payment') }}</div>
                                            <div class="co-head-desc">{{ translate('Choose payment method & place your order') }}</div>
                                        </div>
                                    </div>

                                    <i class="las la-angle-down co-chevron"></i>
                                </div>

                                <div id="collapsePaymentInfo" class="collapse show"
                                     aria-labelledby="headingPaymentInfo"
                                     data-parent="#accordioncCheckoutInfo">
                                    <div class="co-body" id="payment_info">

                                        @include('frontend.partials.cart.payment_info', ['carts' => $carts, 'total' => $total])

                                        <!-- Agree Box -->
                                        <div class="agree-wrap">
                                            <label class="aiz-checkbox mb-2">
                                                <input type="checkbox" required id="agree_checkbox" onchange="stepCompletionPaymentInfo()">
                                                <span class="aiz-square-check"></span>
                                                <span>{{ translate('I agree to the') }}</span>
                                            </label>

                                            <div>
                                                <a href="{{ route('terms') }}">{{ translate('terms and conditions') }}</a>,
                                                <a href="{{ route('returnpolicy') }}">{{ translate('return policy') }}</a> &
                                                <a href="{{ route('privacypolicy') }}">{{ translate('privacy policy') }}</a>
                                            </div>
                                        </div>

                                        <div class="row align-items-center pt-3">
                                            <div class="col-6">
                                                <a href="{{ route('home') }}" class="btn-link-brand">
                                                    <i class="las la-arrow-left fs-16"></i>
                                                    {{ translate('Return to shop') }}
                                                </a>
                                            </div>
                                            <div class="col-6 text-right">
                                                <button type="button" onclick="submitOrder(this)" id="submitOrderBtn"
                                                        class="btn btn-brand">
                                                    {{ translate('Complete Order') }}
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4 mt-4 mt-lg-0" id="cart_summary">
                    <div class="summary-sticky">

                        <div class="summary-offer mb-3">
                            <div class="title">{{ translate('Best offers auto-applied') }}</div>
                            <div class="desc">
                                {{ translate('Prepaid tiers use subtotal; coupons, and prepaid show in savings. COD skips prepaid discount.') }}
                            </div>
                        </div>

                        @include('frontend.partials.cart.cart_summary', ['proceed' => 0, 'carts' => $carts])

                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@section('modal')
    <!-- Address Modal -->
    @if(Auth::check())
        @include('frontend.partials.address.address_modal')
    @endif
@endsection

@section('script')
    <script type="text/javascript">
        var carrierCount=0;

        $(document).ready(function() {
            $(".online_payment").click(function() {
                $('#manual_payment_description').parent().addClass('d-none');
            });
            toggleManualPaymentData($('input[name=payment_option]:checked').data('id'));
            recalcPrepaidTotals();

            // Chevron rotation state (UI)
            $('#accordioncCheckoutInfo .collapse').on('shown.bs.collapse', function () {
                $(this).prev().removeClass('collapsed');
            }).on('hidden.bs.collapse', function () {
                $(this).prev().addClass('collapsed');
            });
            $('#accordioncCheckoutInfo .collapse').each(function(){
                if(!$(this).hasClass('show')) $(this).prev().addClass('collapsed');
            });
        });

        var minimum_order_amount_check = {{ get_setting('minimum_order_amount_check') == 1 ? 1 : 0 }};
        var minimum_order_amount =
            {{ get_setting('minimum_order_amount_check') == 1 ? get_setting('minimum_order_amount') : 0 }};

        function use_wallet() {
            $('input[name=payment_option]').val('wallet');
            if ($('#agree_checkbox').is(":checked")) {
                ;
                if (minimum_order_amount_check && $('#sub_total').val() < minimum_order_amount) {
                    AIZ.plugins.notify('danger',
                        '{{ translate('You order amount is less then the minimum order amount') }}');
                } else {
                    var allIsOk = false;
                    var isOkShipping = stepCompletionShippingInfo();
                    var isOkDelivery = stepCompletionDeliveryInfo();
                    var isOkPayment = stepCompletionWalletPaymentInfo();
                    if(isOkShipping && isOkDelivery && isOkPayment) {
                        allIsOk = true;
                    }else{
                        AIZ.plugins.notify('danger', '{{ translate("Please fill in all mandatory fields!") }}');
                        $('#checkout-form [required]').each(function (i, el) {
                            if ($(el).val() == '' || $(el).val() == undefined) {
                                var is_trx_id = $('.d-none #trx_id').length;
                                if(($(el).attr('name') != 'trx_id') || is_trx_id == 0){
                                    $(el).focus();
                                    $(el).scrollIntoView({behavior: "smooth", block: "center"});
                                    return false;
                                }
                            }
                        });
                    }

                    if (allIsOk) {
                        $('#checkout-form').submit();
                    }
                }
            } else {
                AIZ.plugins.notify('danger', '{{ translate('You need to agree with our policies') }}');
            }
        }

        function submitOrder(el) {
            $(el).prop('disabled', true);
            if ($('#agree_checkbox').is(":checked")) {
                if (minimum_order_amount_check && $('#sub_total').val() < minimum_order_amount) {
                    AIZ.plugins.notify('danger',
                        '{{ translate('You order amount is less then the minimum order amount') }}');
                } else {
                    var offline_payment_active = '{{ addon_is_activated('offline_payment') }}';
                    if (offline_payment_active == '1' && $('.offline_payment_option').is(":checked") && $('#trx_id')
                        .val() == '') {
                        AIZ.plugins.notify('danger', '{{ translate('You need to put Transaction id') }}');
                        $(el).prop('disabled', false);
                    } else {
                        var allIsOk = false;
                        var isOkShipping = stepCompletionShippingInfo();
                        var isOkDelivery = stepCompletionDeliveryInfo();
                        var isOkPayment = stepCompletionPaymentInfo();
                        if(isOkShipping && isOkDelivery && isOkPayment) {
                            allIsOk = true;
                        }else{
                            AIZ.plugins.notify('danger', '{{ translate("Please fill in all mandatory fields!") }}');
                            $('#checkout-form [required]').each(function (i, el) {
                                if ($(el).val() == '' || $(el).val() == undefined) {
                                    var is_trx_id = $('.d-none #trx_id').length;
                                    if(($(el).attr('name') != 'trx_id') || is_trx_id == 0){
                                        $(el).focus();
                                        $(el).scrollIntoView({behavior: "smooth", block: "center"});
                                        return false;
                                    }
                                }
                            });
                        }

                        if (allIsOk) {
                            $('#checkout-form').submit();
                        }
                    }
                }
            } else {
                AIZ.plugins.notify('danger', '{{ translate('You need to agree with our policies') }}');
                $(el).prop('disabled', false);
            }
        }

        function toggleManualPaymentData(id) {
            if (typeof id != 'undefined') {
                $('#manual_payment_description').parent().removeClass('d-none');
                $('#manual_payment_description').html($('#manual_payment_info_' + id).html());
            }
        }

        function updatePrepaidDiscountSummary(res) {
            if (res.discount_amount > 0) {
                $('#prepaid-discount-label').text(res.discount_label || '{{ translate('Prepaid Discount') }}');
                $('#prepaid-discount-amount').text(res.formatted.discount);
                $('#prepaid-discount-row').removeClass('d-none');
            } else {
                $('#prepaid-discount-row').addClass('d-none');
            }
            $('#summary-subtotal').text(res.formatted.subtotal);
            $('#summary-shipping').text(res.formatted.shipping);
            $('#summary-total').text(res.formatted.grand_total);
        }

        function recalcPrepaidTotals() {
            var paymentType = $('input[name="payment_option"]:checked').val();
            if (!paymentType) { return; }

            $.post('{{ route('checkout.recalculate') }}', {
                _token: '{{ csrf_token() }}',
                payment_type: paymentType
            }).done(function(res){
                updatePrepaidDiscountSummary(res);
            });
        }

        // coupon apply
        $(document).on("click", "#coupon-apply", function() {
            @if (Auth::check())
                @if(Auth::user()->user_type != 'customer')
                    AIZ.plugins.notify('warning', "{{ translate('Please Login as a customer to apply coupon code.') }}");
                    return false;
                @endif

                var data = new FormData($('#apply-coupon-form')[0]);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{ route('checkout.apply_coupon_code') }}",
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data, textStatus, jqXHR) {
                        AIZ.plugins.notify(data.response_message.response, data.response_message.message);
                        $("#cart_summary").html(data.html);
                        recalcPrepaidTotals();
                    }
                });
            @else
                $('#login_modal').modal('show');
            @endif
        });

        // coupon remove
        $(document).on("click", "#coupon-remove", function() {
            @if (Auth::check() && Auth::user()->user_type == 'customer')
                var data = new FormData($('#remove-coupon-form')[0]);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{ route('checkout.remove_coupon_code') }}",
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data, textStatus, jqXHR) {
                        $("#cart_summary").html(data);
                        recalcPrepaidTotals();
                    }
                });
            @endif
        });

        function updateDeliveryAddress(id, city_id = 0, area_id=0) {
            $('.aiz-refresh').addClass('active');
            $.post('{{ route('checkout.updateDeliveryAddress') }}', {
                _token: AIZ.data.csrf,
                address_id: id,
                city_id: city_id,
                area_id: area_id
            }, function(data) {
                $('#delivery_info').html(data.delivery_info);
                $('#cart_summary').html(data.cart_summary);
                $('.aiz-refresh').removeClass('active');
                carrierCount = data.carrier_count;
                checkCarrerShippingInfo();
                recalcPrepaidTotals();
            });

            AIZ.plugins.bootstrapSelect("refresh");
        }

        function stepCompletionShippingInfo() {
            var headColor = '#9d9da6';
            var btnDisable = true;
            var allOk = false;
            @if (Auth::check())
                var length = $('input[name="address_id"]:checked').length;
                if (length > 0) {
                    headColor = '#15a405';
                    btnDisable = false;
                    allOk = true;
                }
            @else
                var count = 0;
                var length = $('#shipping_info [required]').length;
                $('#shipping_info [required]').each(function (i, el) {
                    if ($(el).val() != '' && $(el).val() != undefined && $(el).val() != null) {
                        count += 1;
                    }
                });
                if (count == length) {
                    headColor = '#15a405';
                    btnDisable = false;
                    allOk = true;
                }
            @endif

            $('#headingShippingInfo svg *').css('fill', headColor);
            $("#submitOrderBtn").prop('disabled', btnDisable);
            return allOk;
        }

        $('#shipping_info [required]').each(function (i, el) {
            $(el).change(function(){
                if ($(el).attr('name') == 'address_id') {
                    updateDeliveryAddress($(el).val());
                }
                @if (get_setting('shipping_type') == 'area_wise_shipping')
                    if ($(el).attr('name') == 'city_id') {
                        let country_id = $('select[name="country_id"]').length? $('select[name="country_id"]').val() : $('input[name="country_id"]').val();
                        let city_id = $(this).val();
                        updateDeliveryAddress(country_id, city_id);
                    }
                @endif
                stepCompletionShippingInfo();
            });
        });

        $('select[name="area_id"].guest-checkout').change(function () {
            let country_id = $('select[name="country_id"]').length
                ? $('select[name="country_id"]').val()
                : $('input[name="country_id"]').val();
            let city_id = $('select[name="city_id"]').val();
            let area_id = $(this).val();

            if (area_id) {
                updateDeliveryAddress(country_id, city_id, area_id);
            } else {
                updateDeliveryAddress(country_id, city_id);
            }

            stepCompletionShippingInfo();
        });

        function stepCompletionDeliveryInfo() {
            var headColor = '#9d9da6';
            var btnDisable = true;
            var allOk = false;
            var content = $('#delivery_info [required]');
            if (content.length > 0) {
                var content_checked = $('#delivery_info [required]:checked');
                if (content_checked.length > 0) {
                    content_checked.each(function (i, el) {
                        allOk = false;
                        if($(el).val() == 'carrier'){
                            var owner = $(el).attr('data-owner');
                            if ($('input[name=carrier_id_'+owner+']:checked').length > 0) {
                                allOk = true;
                            }
                        }else if($(el).val() == 'pickup_point'){
                            var owner = $(el).attr('data-owner');
                            if ($('select[name="pickup_point_id_'+owner+'"]').val() != '') {
                                allOk = true;
                            }
                        }else{
                            allOk = true;
                        }

                        if(allOk == false) {
                            return false;
                        }
                    });

                    if (allOk) {
                        headColor = '#15a405';
                        btnDisable = false;
                    }
                }
            }else{
                allOk = true
                headColor = '#15a405';
                btnDisable = false;
            }

            $('#headingDeliveryInfo svg *').css('fill', headColor);
            $("#submitOrderBtn").prop('disabled', btnDisable);
            return allOk;
        }

        function updateDeliveryInfo(shipping_type, type_id, user_id, country_id = 0, city_id = 0) {
            @if (get_setting('shipping_type') == 'area_wise_shipping' || get_setting('shipping_type') == 'carrier_wise_shipping')
                country_id = $('select[name="country_id"]').val() != null ? $('select[name="country_id"]').val() : 0;
                city_id = $('select[name="city_id"]').val() != null ? $('select[name="city_id"]').val() : 0;
            @endif
            $('.aiz-refresh').addClass('active');
            $.post('{{ route('checkout.updateDeliveryInfo') }}', {
                _token: AIZ.data.csrf,
                shipping_type: shipping_type,
                type_id: type_id,
                user_id: user_id,
                country_id: country_id,
                city_id: city_id
            }, function(data) {
                $('#cart_summary').html(data);
                checkCarrerShippingInfo();
                stepCompletionDeliveryInfo();
                $('.aiz-refresh').removeClass('active');
                recalcPrepaidTotals();
            });
            AIZ.plugins.bootstrapSelect("refresh");
        }

        function show_pickup_point(el, user_id) {
            var type = $(el).val();
            var target = $(el).data('target');
            var type_id = null;

            if(type == 'home_delivery' || type == 'carrier'){
                if(!$(target).hasClass('d-none')){
                    $(target).addClass('d-none');
                }
                $('.carrier_id_'+user_id).removeClass('d-none');
            }else{
                $(target).removeClass('d-none');
                $('.carrier_id_'+user_id).addClass('d-none');
            }

            if(type == 'carrier'){
                type_id = $('input[name=carrier_id_'+user_id+']:checked').val();
            }else if(type == 'pickup_point'){
                type_id = $('select[name=pickup_point_id_'+user_id+']').val();
            }
            updateDeliveryInfo(type, type_id, user_id);
        }

        function stepCompletionPaymentInfo() {
            var headColor = '#9d9da6';
            var btnDisable = true;
            var payment = false;
            var agree = false;
            var allOk = false;
            var length = $('input[name="payment_option"]:checked').length;
            if(length > 0){
                if ($('input[name="payment_option"]:checked').hasClass('offline_payment_option')) {
                    if ($('#trx_id').val() != '' && $('#trx_id').val() != undefined && $('#trx_id').val() != null) {
                        payment = true;
                    }
                } else {
                    payment = true;
                }

                if ($('#agree_checkbox').is(":checked")){
                    agree = true;
                }

                if (payment && agree) {
                    headColor = '#15a405';
                    btnDisable = false;
                    allOk = true;
                }
            }

            $('#headingPaymentInfo svg *').css('fill', headColor);
            $("#submitOrderBtn").prop('disabled', btnDisable);
            return allOk;
        }

        function stepCompletionWalletPaymentInfo() {
            var headColor = '#9d9da6';
            var btnDisable = true;
            var allOk = false;
            if ($('#agree_checkbox').is(":checked")){
                headColor = '#15a405';
                btnDisable = false;
                allOk = true;
            }

            $('#headingPaymentInfo svg *').css('fill', headColor);
            $("#submitOrderBtn").prop('disabled', btnDisable);
            return allOk;
        }

        $('input[name="payment_option"]').change(function(){
            stepCompletionPaymentInfo();
            recalcPrepaidTotals();
        });

        function checkCarrerShippingInfo(){
            const shippingType = @json(get_setting('shipping_type'));
            const isDisabled = carrierCount === 0;
            let carrierSelected = false;
            let pickupSelected = false;
            $('.shipping-type-radio').each(function () {
                if ($(this).is(':checked') && $(this).val() === 'carrier') {
                    carrierSelected = true;
                }
            });
            $('.shipping-type-radio').each(function () {
                if ($(this).is(':checked') && $(this).val() === 'pickup_point') {
                    pickupSelected = true;
                }
            });
            if(shippingType == 'carrier_wise_shipping' && carrierSelected){
                if (carrierCount === 0) {
                    if( (carrierSelected && pickupSelected) || (carrierSelected && !pickupSelected) ){
                        $('#submitOrderBtn').prop('disabled', true);
                        $('#agree_checkbox').prop('checked', false).prop('disabled', true);
                        $('.online_payment, .offline_payment_option').prop('checked', false).prop('disabled', true);
                    }
                } else {
                    $('#agree_checkbox').prop('disabled', false);
                    $('.online_payment, .offline_payment_option').prop('disabled', false);
                }
            }else{
                $('#agree_checkbox').prop('disabled', false);
                $('.online_payment, .offline_payment_option').prop('disabled', false);
            }
        }

        $(document).ready(function(){
            carrierCount = parseInt(document.getElementById('carrierCount')?.value || 0);
            checkCarrerShippingInfo();
            stepCompletionShippingInfo();
            stepCompletionDeliveryInfo();
            stepCompletionPaymentInfo();
        });
    </script>

    @include('frontend.partials.address.address_js')

    @if(get_active_countries()->count() == 1)
        <script>
            $(document).ready(function() {
                @if(get_setting('has_state') == 1)
                    get_states(@json(get_active_countries()[0]->id));
                @else
                    get_city_by_country(@json(get_active_countries()[0]->id));
                @endif
            });
            @if(get_setting('shipping_type') == 'carrier_wise_shipping' && !Auth::check() )
                updateDeliveryAddress({{ get_active_countries()[0]->id }});
            @endif
        </script>
    @endif

    @if (get_setting('google_map') == 1)
        @include('frontend.partials.google_map')
    @endif
@endsection
