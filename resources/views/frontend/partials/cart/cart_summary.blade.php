<div class="z-3 sticky-top-lg">
    <div class="card border os-card">

        @php
            $subtotal_for_min_order_amount = 0;
            $subtotal = 0;
            $purchase_subtotal = 0;
            $tax = 0;
            $product_shipping_cost = 0;
            $shipping = 0;
            $coupon_code = null;
            $coupon_discount = 0;
            $total_point = 0;
            $prepaid_rules = \App\Models\PrepaidDiscount::where('is_active', true)->orderBy('priority')->orderByDesc('percent')->get();
            $applied_prepaid_rule = null;
            $applied_prepaid_amount = 0;
            $next_prepaid_rule = null;
        @endphp

        @foreach ($carts as $key => $cartItem)
            @php
                $product = get_single_product($cartItem['product_id']);
                $subtotal_for_min_order_amount += cart_product_price($cartItem, $cartItem->product, false, false) * $cartItem['quantity'];
                $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                $purchase_subtotal += (($cartItem->purchase_price ?? $product->purchase_price ?? 0) * $cartItem['quantity']);
                $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                $product_shipping_cost = $cartItem['shipping_cost'];
                $shipping += $product_shipping_cost;

                if (get_setting('coupon_system') == 1 && $cartItem->coupon_applied == 1) {
                    $coupon_code = $cartItem->coupon_code;
                    $coupon_discount = $carts->sum('discount');
                }

                if (addon_is_activated('club_point')) {
                    $total_point += $product->earn_point * $cartItem['quantity'];
                }
            @endphp
        @endforeach

        @php
            $total = $subtotal + $tax + $shipping;
            if (Session::has('club_point')) {
                $total -= Session::get('club_point');
            }
            if ($coupon_discount > 0) {
                $total -= $coupon_discount;
            }

            $purchase_subtotal = max($purchase_subtotal, 0);
            $instant_savings = max($purchase_subtotal - $subtotal, 0);
            $savings_vs_purchase = max($subtotal - $purchase_subtotal, 0);
            $redeem_savings = Session::has('club_point') ? Session::get('club_point') : 0;

            // Prepaid rule selection (info-only; actual application depends on payment choice)
            $applied_prepaid_rule = $prepaid_rules->first(function ($rule) use ($subtotal) {
                return $rule->min_amount <= $subtotal && (is_null($rule->max_amount) || $rule->max_amount >= $subtotal);
            });
            if ($applied_prepaid_rule) {
                $applied_prepaid_amount = round(($subtotal * $applied_prepaid_rule->percent) / 100, 2);
            }

            // Next rule: pick the smallest higher percent tier; if none applied, pick next threshold above subtotal
            if ($applied_prepaid_rule) {
                $next_prepaid_rule = $prepaid_rules
                    ->filter(function ($rule) use ($applied_prepaid_rule) {
                        return $rule->percent > $applied_prepaid_rule->percent;
                    })
                    ->sortBy('percent')
                    ->first();
            } else {
                $next_prepaid_rule = $prepaid_rules
                    ->filter(function ($rule) use ($subtotal) {
                        return $rule->min_amount > $subtotal;
                    })
                    ->sortBy('min_amount')
                    ->first();
            }

            // Shipping savings (positive story when shipping is free)
            $shipping_savings = $shipping == 0 ? 50 : 0; // fallback saved shipping value used in earlier UI

            $total_savings = $instant_savings + $coupon_discount + $applied_prepaid_amount + $shipping_savings + $redeem_savings;
            $total_savings = max($total_savings, 0);

            $amount_needed = $next_prepaid_rule ? max($next_prepaid_rule->min_amount - $subtotal, 0) : 0;
            $progress_max = $next_prepaid_rule ? $next_prepaid_rule->min_amount : ($subtotal > 0 ? $subtotal : 1);
            $progress_percent = min(100, round(($subtotal / $progress_max) * 100, 2));
        @endphp

        {{-- Header --}}
        <div class="card-header os-header">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="os-title">{{ translate('Order Summary') }}</div>
                    <div class="os-subtitle">
                        {{ sprintf('%02d', count($carts)) }} {{ translate('items') }}
                    </div>
                </div>
                @if (addon_is_activated('club_point') && $total_point > 0)
                    <span class="badge badge-inline badge-secondary-base fs-12 rounded px-2 text-white">
                        {{ translate('Club Points') }}: {{ $total_point }}
                    </span>
                @endif
            </div>

            {{-- Minimum Order Amount --}}
            @if (get_setting('minimum_order_amount_check') == 1 && $subtotal_for_min_order_amount < get_setting('minimum_order_amount'))
                <div class="mt-3 os-alert">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="fw-700">
                            {{ translate('Minimum Order Amount') }}
                        </div>
                        <div class="fw-900">
                            {{ single_price(get_setting('minimum_order_amount')) }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="card-body os-body">
            <input type="hidden" id="sub_total" value="{{ $subtotal }}">

            {{-- Savings highlight --}}
            <div class="os-savings-box">
                    <div class="d-flex align-items-center">
                <span class="os-savings-icon" aria-hidden="true"></span>
                <div>
                    <div class="os-savings-label">{{ translate('You saved') }}</div>
                    <div class="os-savings-amount">{{ single_price($total_savings) }}</div>
                </div>
            </div>
                <button class="os-link-btn collapsed" type="button"
                        data-toggle="collapse" data-target="#os-savings-breakdown"
                        aria-expanded="false" aria-controls="os-savings-breakdown">
                    <span class="os-link-label" data-toggle-text="{{ translate('Hide breakdown') }}">
                        {{ translate('View breakdown') }}
                    </span>
                    <i class="las la-angle-down ml-1"></i>
                </button>
            </div>

            {{-- Payable --}}
            <div class="os-payable">
                <div>
                    <div class="os-payable-label">{{ translate('Payable Amount') }}</div>
                    <div class="os-payable-note">{{ translate('Inclusive of taxes') }}</div>
                </div>
                <div class="os-payable-value" id="summary-total">{{ single_price($total) }}</div>
            </div>

            {{-- Core rows --}}
            <div class="os-breakdown mb-3">
                <div class="os-row">
                    <div class="os-row-left">
                        <div class="os-row-title">{{ translate('Items total') }}</div>
                        <div class="os-row-meta">{{ sprintf('%02d', count($carts)) }} {{ translate('Products') }}</div>
                    </div>
                    <div class="os-row-right" id="summary-subtotal">{{ single_price($subtotal) }}</div>
                </div>

                <div class="os-row">
                    <div class="os-row-left">
                        <div class="os-row-title">{{ translate('Shipping') }}</div>
                        @if ($shipping == 0)
                            <div class="os-row-meta text-success">{{ translate('Free Shipping Unlocked!') }}</div>
                        @endif
                    </div>
                    <div class="os-row-right">
                        @if ($shipping == 0)
                            <div class="text-success fw-700">{{ translate('Free') }}</div>
                            @if ($shipping_savings > 0)
                                <div class="os-row-meta text-success">{{ translate('Saved') }} {{ single_price($shipping_savings) }}</div>
                            @endif
                            <span id="summary-shipping" class="d-none">{{ single_price($shipping) }}</span>
                        @else
                            <span id="summary-shipping">{{ single_price($shipping) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Savings breakdown --}}
            <div class="collapse os-breakdown-wrap" id="os-savings-breakdown">
                <div class="os-breakdown">
                    <div class="os-row">
                        <div class="os-row-left">
                            <div class="os-row-title">{{ translate('List price') }}</div>
                        </div>
                        <div class="os-row-right text-secondary"><del>{{ single_price($purchase_subtotal) }}</del></div>
                    </div>

                    @if ($instant_savings > 0)
                        <div class="os-row os-row-discount">
                            <div class="os-row-left">
                                <div class="os-row-title">{{ translate('Instant discount') }}</div>
                            </div>
                            <div class="os-row-right">- {{ single_price($instant_savings) }}</div>
                        </div>
                    @endif

                    @if ($coupon_discount > 0)
                        <div class="os-row os-row-discount">
                            <div class="os-row-left">
                                <div class="os-row-title">{{ translate('Coupon discount') }}</div>
                                @if ($coupon_code)
                                    <div class="os-row-meta">{{ translate('Code') }}: {{ $coupon_code }}</div>
                                @endif
                            </div>
                            <div class="os-row-right">- {{ single_price($coupon_discount) }}</div>
                        </div>
                    @endif

                    <div class="os-row os-row-discount {{ $applied_prepaid_amount > 0 ? '' : 'd-none' }}" id="prepaid-discount-row">
                        <div class="os-row-left">
                            <div class="fw-400">
                                {{ $applied_prepaid_rule ? $applied_prepaid_rule->title : translate('Prepaid Discount') }}
                            </div>
                            <div class="os-row-meta">{{ translate('Pay online & save instantly') }}</div>
                        </div>
                        <div class="os-row-right">- {{ single_price($applied_prepaid_amount) }}</div>
                    </div>

                    @if ($shipping_savings > 0)
                        <div class="os-row os-row-discount">
                            <div class="os-row-left">
                                <div class="os-row-title">{{ translate('Shipping savings') }}</div>
                            </div>
                            <div class="os-row-right">- {{ single_price($shipping_savings) }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Upsell progress --}}
            @if ($next_prepaid_rule || $applied_prepaid_rule)
                <div class="os-progress-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex flex-column">
                            <span class="os-row-title mb-0">
                                {{ $next_prepaid_rule ? translate('Buy more & save') : translate('Prepaid savings unlocked') }}
                            </span>
                            <span class="os-row-meta">
                                @if ($next_prepaid_rule)
                                    {{ translate('Add') }} <b>{{ single_price($amount_needed) }}</b>
                                    {{ translate('to unlock') }} <b>{{ rtrim(rtrim($next_prepaid_rule->percent, '0'), '.') }}%</b>
                                    {{ translate('prepaid discount') }}
                                @elseif ($applied_prepaid_rule)
                                    {{ translate('You unlocked') }} {{ rtrim(rtrim($applied_prepaid_rule->percent, '0'), '.') }}% {{ translate('prepaid savings') }}
                                @endif
                            </span>
                        </div>
                        <span class="fs-12 rounded px-2" style="background-color: #28a745; color: #e6f9f0;">
                            {{ $next_prepaid_rule ? translate('Progress') : translate('Unlocked') }}
                        </span>
                    </div>
                    <div class="progress os-progress-bar" role="progressbar"
                         aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress_percent }}">
                        <div class="progress-bar" style="width: {{ $progress_percent }}%;"></div>
                    </div>
                </div>
            @endif

            {{-- Coupon --}}
            @if (get_setting('coupon_system') == 1)
                @if ($coupon_discount > 0 && $coupon_code)
                    <div class="mt-3">
                        <form id="remove-coupon-form" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="proceed" value="{{ $proceed }}">

                            <div class="os-coupon-applied d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-white fw-800" style="font-size:13px;">
                                        {{ translate('Coupon Applied') }}: {{ $coupon_code }}
                                    </div>
                                    <div class="text-white" style="font-size:12px;opacity:.9;">
                                        {{ translate('You saved') }} {{ single_price($coupon_discount) }}
                                    </div>
                                </div>
                                <button type="button" id="coupon-remove" class="btn os-btn-light">
                                    {{ translate('Change') }}
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="mt-3">
                        <form id="apply-coupon-form" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="proceed" value="{{ $proceed }}">

                            <div class="os-coupon-wrap">
                                <input type="text" class="form-control os-input"
                                       name="code"
                                       onkeydown="return event.key != 'Enter';"
                                       placeholder="{{ translate('Have a coupon? Enter code') }}" required>

                                <button type="button" id="coupon-apply" class="btn os-btn-primary">
                                    {{ translate('Apply') }}
                                </button>
                            </div>

                            @if (!auth()->check())
                                <small class="d-block mt-2 text-secondary" style="font-size:12px;">
                                    {{ translate('You must Login as customer to apply coupon') }}
                                </small>
                            @endif
                        </form>
                    </div>
                @endif
            @endif

            {{-- Trust row --}}
            <div class="os-trust-row">
                <i class="las la-shield-alt"></i>
                <span>{{ translate('100% Secure Checkout • Easy Returns • Fast Dispatch') }}</span>
            </div>

            {{-- Checkout --}}
            @if ($proceed == 1)
                <div class="mt-4">
                    <a href="{{ route('checkout') }}" class="btn os-btn-checkout btn-block">
                        {{ translate('Proceed to Checkout') }} ({{ sprintf('%02d', count($carts)) }})
                    </a>
                </div>
            @endif

        </div>
    </div>
</div>

<style>
    /* Theme colors */
    :root{
        --os-red:#c70a04;
        --os-white:#ffffff;
        --os-black:#212121;
        --os-muted:rgba(33,33,33,.65);
    }

    .os-card{
        border-radius:16px !important;
        overflow:hidden;
        box-shadow:0 10px 28px rgba(0,0,0,.08);
    }
    .os-header{
        background:var(--os-white);
        border-bottom:1px solid rgba(0,0,0,.06);
        padding:18px 18px 14px;
    }
    .os-title{
        font-size:16px;
        font-weight:900;
        color:var(--os-black);
    }
    .os-subtitle{
        font-size:12px;
        color:var(--os-muted);
        margin-top:4px;
    }
    .os-alert{
        background:rgba(199,10,4,.08);
        border:1px solid rgba(199,10,4,.22);
        color:var(--os-black);
        border-radius:12px;
        padding:10px 12px;
        font-size:12px;
    }
    .os-body{ padding:16px 18px 18px; }

    /* Savings */
    .os-savings-box{
        background:rgba(199,10,4,.08);
        border:1px solid rgba(199,10,4,.18);
        border-radius:14px;
        padding:12px 14px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        margin-bottom:12px;
    }
    .os-savings-label{
        font-size:12px;
        text-transform:uppercase;
        letter-spacing:.4px;
        color:var(--os-black);
        font-weight:800;
        opacity:.8;
    }
    .os-savings-amount{
        font-size:20px;
        font-weight:1000;
        color:var(--os-red);
        line-height:1.1;
    }
    .os-link-btn{
        background:transparent;
        border:none;
        padding:4px 0;
        font-size:12px;
        font-weight:800;
        color:var(--os-black);
        display:flex;
        align-items:center;
    }
    .os-link-btn.collapsed i{ transform:rotate(-90deg); transition:.2s ease; }
    .os-link-btn i{ transition:.2s ease; }

    /* Payable */
    .os-payable{
        display:flex;
        align-items:center;
        justify-content:space-between;
        background:#fff;
        border:1px solid rgba(0,0,0,.08);
        border-radius:14px;
        padding:12px 14px;
        margin-bottom:12px;
    }
    .os-payable-label{
        font-size:13px;
        font-weight:800;
        color:var(--os-black);
    }
    .os-payable-note{
        font-size:12px;
        color:var(--os-muted);
        margin-top:2px;
    }
    .os-payable-value{
        font-size:20px;
        font-weight:1000;
        color:var(--os-red);
    }

    /* Breakdown */
    .os-breakdown{
        border:1px solid rgba(0,0,0,.08);
        border-radius:16px;
        padding:12px;
        background:#fff;
    }
    .os-row{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        padding:10px 0;
    }
    .os-row + .os-row{ border-top:1px dashed rgba(0,0,0,.10); }
    .os-row-left{ min-width:0; padding-right:10px; }
    .os-row-title{ font-size:13px; color:var(--os-black); font-weight:800; }
    .os-row-meta{ font-size:12px; color:var(--os-muted); margin-top:2px; }
    .os-row-right{ font-size:13px; font-weight:900; color:var(--os-black); text-align:right; }
    .os-row-discount .os-row-right{ color:var(--os-red); }

    /* Progress */
    .os-progress-card{
        border:1px dashed rgba(199,10,4,.35);
        background:rgba(199,10,4,.05);
        border-radius:14px;
        padding:12px 14px;
        margin-top:12px;
    }
    .os-progress-bar{
        height:8px;
        background:rgba(0,0,0,.06);
        border-radius:999px;
        overflow:hidden;
    }
    .os-progress-bar .progress-bar{
        background:var(--os-red);
        border-radius:999px;
    }

    /* Coupon */
    .os-coupon-wrap{
        display:flex;
        gap:10px;
        align-items:center;
    }
    .os-input{
        border-radius:12px !important;
        border:1px solid rgba(0,0,0,.12) !important;
        height:44px;
    }
    .os-btn-primary{
        background:var(--os-red) !important;
        border-color:var(--os-red) !important;
        color:var(--os-white) !important;
        border-radius:12px !important;
        height:44px;
        font-weight:900;
        padding:0 14px;
    }
    .os-coupon-applied{
        background:var(--os-red);
        border-radius:16px;
        padding:14px;
    }
    .os-btn-light{
        background:#fff !important;
        border:1px solid rgba(255,255,255,.55) !important;
        border-radius:12px !important;
        font-weight:900;
        height:40px;
    }

    /* Checkout */
    .os-btn-checkout{
        background:var(--os-black) !important;
        border-color:var(--os-black) !important;
        color:var(--os-white) !important;
        border-radius:14px !important;
        padding:14px 16px !important;
        font-weight:1000;
    }
    .os-btn-checkout:hover{ filter:brightness(1.05); }

    /* Trust row */
    .os-trust-row{
        display:flex;
        align-items:center;
        gap:8px;
        margin-top:16px;
        font-size:12px;
        color:var(--os-muted);
        font-weight:800;
    }
    .os-trust-row i{ color:var(--os-red); font-size:16px; }

    @media(max-width: 991px){
        .os-card{ box-shadow:none; border-radius:12px !important; }
        .os-payable-value{ font-size:18px; }
        .os-savings-amount{ font-size:18px; }
    }
</style>
