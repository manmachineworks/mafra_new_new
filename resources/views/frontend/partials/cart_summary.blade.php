<div class="card rounded-lg border-0 shadow-sm">

    <div class="card-header bg-white border-0 pt-4 pb-2 d-flex align-items-start justify-content-between">
        <div>
            <h3 class="fs-16 fw-800 mb-1">{{ translate('Order Summary') }}</h3>
            <div class="text-muted fs-12">{{ translate('Review price & savings before checkout') }}</div>
        </div>
        <div class="text-right">
            <span class="badge badge-inline badge-primary fs-12 rounded px-2">
                {{ count($carts) }} {{ translate('Items') }}
            </span>
        </div>
    </div>

    <!-- Club point -->
    @if (addon_is_activated('club_point'))
    <div class="px-4 pt-1 w-100 d-flex align-items-center justify-content-between">
        <h3 class="fs-14 fw-700 mb-0">{{ translate('Total Clubpoint') }}</h3>
        <div class="text-right">
            <span class="badge badge-inline badge-secondary-base fs-12 rounded-0 px-2 text-white">
                @php
                    $total_point = 0;
                @endphp
                @foreach ($carts as $key => $cartItem)
                    @php
                        $product = get_single_product($cartItem['product_id']);
                        $total_point += $product->earn_point * $cartItem['quantity'];
                    @endphp
                @endforeach

                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" class="mr-2">
                    <g id="Group_23922" data-name="Group 23922" transform="translate(-973 -633)">
                      <circle id="Ellipse_39" data-name="Ellipse 39" cx="6" cy="6" r="6" transform="translate(973 633)" fill="#fff"/>
                      <g id="Group_23920" data-name="Group 23920" transform="translate(973 633)">
                        <path id="Path_28698" data-name="Path 28698" d="M7.667,3H4.333L3,5,6,9,9,5Z" transform="translate(0 0)" fill="#f3af3d"/>
                        <path id="Path_28699" data-name="Path 28699" d="M5.33,3h-1L3,5,6,9,4.331,5Z" transform="translate(0 0)" fill="#f3af3d" opacity="0.5"/>
                        <path id="Path_28700" data-name="Path 28700" d="M12.666,3h1L15,5,12,9l1.664-4Z" transform="translate(-5.995 0)" fill="#f3af3d"/>
                      </g>
                    </g>
                </svg>
                {{ $total_point }}
            </span>
        </div>
    </div>
    @endif

    <div class="card-body pt-0">
        @php
            $coupon_discount = 0;
            $coupon_code = null;
        @endphp
        @if (get_setting('coupon_system') == 1)
            @foreach ($carts as $key => $cartItem)
                @if ($cartItem->coupon_applied == 1)
                    @php
                        $coupon_code = $cartItem->coupon_code;
                        break;
                    @endphp
                @endif
            @endforeach
            @php
                $coupon_discount = $carts->sum('discount');
            @endphp
        @endif
        <!-- Products Info -->
        <table class="table">
            <thead>
                <tr>
                    <th class="product-name border-top-0 border-bottom-1 pl-0 fs-12 fw-400 opacity-60">{{ translate('Product') }}</th>
                    <th class="product-total text-right border-top-0 border-bottom-1 pr-0 fs-12 fw-400 opacity-60">{{ translate('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                    $purchase_subtotal = 0;
                    $tax = 0;
                    $shipping = 0;
                    $product_shipping_cost = 0;
                    $prepaid_hint = null;
                @endphp
                @foreach ($carts as $key => $cartItem)
                    @php
                        $product = get_single_product($cartItem['product_id']);
                        $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                        $purchase_subtotal += (($cartItem->purchase_price ?? $product->purchase_price ?? 0) * $cartItem['quantity']);
                        $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                        $product_shipping_cost = $cartItem['shipping_cost'];

                        $shipping += $product_shipping_cost;

                        $product_name_with_choice = $product->getTranslation('name');
                        if ($cartItem['variant'] != null) {
                            $product_name_with_choice = $product->getTranslation('name') . ' - ' . $cartItem['variant'];
                        }
                        if ($prepaid_hint === null) {
                            $prepaid_hint = translate('Prepaid tiers apply on subtotal; COD has no prepaid discount.');
                        }
                    @endphp
                    <tr class="cart_item">
                        <td class="product-name pl-0 fs-14 text-dark fw-400 border-top-0 border-bottom">
                            {{ $product_name_with_choice }}
                            <strong class="product-quantity">
                                × {{ $cartItem['quantity'] }}
                            </strong>
                        </td>
                        <td class="product-total text-right pr-0 fs-14 text-primary fw-600 border-top-0 border-bottom">
                            <span
                                class="pl-4 pr-0">{{ single_price(cart_product_price($cartItem, $cartItem->product, false, false) * $cartItem['quantity']) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <input type="hidden" id="sub_total" value="{{ $subtotal }}">

        <table class="table" style="margin-top: 2rem!important;">
            <tfoot>
                <!-- Subtotal -->
                <tr class="cart-subtotal">
                    <th class="pl-0 fs-14 pt-0 pb-2 text-dark fw-600 border-top-0">{{ translate('Subtotal') }}</th>
                    <td class="text-right pr-0 fs-14 pt-0 pb-2 fw-600 text-primary border-top-0">
                        <span class="fw-600">{{ single_price($subtotal) }}</span>
                    </td>
                </tr>
                <tr class="cart-subtotal">
                    <th class="pl-0 fs-13 pt-0 pb-2 text-secondary fw-600 border-top-0">{{ translate('Purchase Subtotal') }}</th>
                    <td class="text-right pr-0 fs-13 pt-0 pb-2 fw-600 text-secondary border-top-0">
                        <span class="fw-600"><del>{{ single_price($purchase_subtotal) }}</del></span>
                    </td>
                </tr>
                @php $savings_vs_purchase = max($subtotal - $purchase_subtotal, 0); @endphp
                @if($savings_vs_purchase > 0)
                <tr class="cart-subtotal">
                    <th class="pl-0 fs-13 pt-0 pb-2 text-success fw-600 border-top-0">{{ translate('Savings vs Purchase') }}</th>
                    <td class="text-right pr-0 fs-13 pt-0 pb-2 fw-600 text-success border-top-0">
                        <span class="fw-600">+ {{ single_price($savings_vs_purchase) }}</span>
                    </td>
                </tr>
                @endif
                <tr class="cart-subtotal">
                    <th class="pl-0 fs-13 pt-0 pb-2 text-secondary fw-600 border-top-0">{{ translate('Purchase Subtotal') }}</th>
                    <td class="text-right pr-0 fs-13 pt-0 pb-2 fw-600 text-secondary border-top-0">
                        <span class="fw-600"><del>{{ single_price($purchase_subtotal) }}</del></span>
                    </td>
                </tr>
                @php $savings_vs_purchase = max($subtotal - $purchase_subtotal, 0); @endphp
                @if($savings_vs_purchase > 0)
                <tr class="cart-subtotal">
                    <th class="pl-0 fs-13 pt-0 pb-2 text-success fw-600 border-top-0">{{ translate('Savings vs Purchase') }}</th>
                    <td class="text-right pr-0 fs-13 pt-0 pb-2 fw-600 text-success border-top-0">
                        <span class="fw-600">+ {{ single_price($savings_vs_purchase) }}</span>
                    </td>
                </tr>
                @endif
                <!-- Tax -->
                <!-- <tr class="cart-shipping">
                    <th class="pl-0 fs-14 pt-0 pb-2 text-dark fw-600 border-top-0">{{ translate('Tax') }}</th>
                    <td class="text-right pr-0 fs-14 pt-0 pb-2 fw-600 text-primary border-top-0">
                        <span class="fw-600">{{ single_price($tax) }}</span>
                    </td>
                </tr> -->
                <!-- Total Shipping -->
                <tr class="cart-shipping">
                    <th class="pl-0 fs-14 pt-0 pb-2 text-dark fw-600 border-top-0">{{ translate('Total Shipping') }}</th>
                    <td class="text-right pr-0 fs-14 pt-0 pb-2 fw-600 text-primary border-top-0">
                        <span class="fw-600">{{ single_price($shipping) }}</span>
                    </td>
                </tr>
                <!-- Redeem point -->
                @if (Session::has('club_point'))
                    <tr class="cart-shipping">
                        <th class="pl-0 fs-14 pt-0 pb-2 text-dark fw-600 border-top-0">{{ translate('Redeem point') }}</th>
                        <td class="text-right pr-0 fs-14 pt-0 pb-2 fw-600 text-primary border-top-0">
                            <span class="fw-600">{{ single_price(Session::get('club_point')) }}</span>
                        </td>
                    </tr>
                @endif
                <!-- Coupon Discount -->
                @if ($coupon_discount > 0)
                    <tr class="cart-shipping">
                        <th class="pl-0 fs-14 pt-0 pb-2 text-dark fw-600 border-top-0">{{ translate('Coupon Discount') }}</th>
                        <td class="text-right pr-0 fs-14 pt-0 pb-2 fw-600 text-primary border-top-0">
                            <span class="fw-600">{{ single_price($coupon_discount) }}</span>
                        </td>
                    </tr>
                @endif
                <tr class="cart-shipping d-none" id="prepaid-row-summary">
                    <th class="pl-0 fs-14 pt-0 pb-2 text-dark fw-600 border-top-0" id="prepaid-label-summary">{{ translate('Prepaid Discount') }}</th>
                    <td class="text-right pr-0 fs-14 pt-0 pb-2 fw-600 text-primary border-top-0">
                        <span class="fw-600" id="prepaid-amount-summary">{{ single_price(0) }}</span>
                    </td>
                </tr>

                @php
                    $total = $subtotal + $tax + $shipping;
                    if (Session::has('club_point')) {
                        $total -= Session::get('club_point');
                    }
                    if ($coupon_discount > 0) {
                        $total -= $coupon_discount;
                    }
                @endphp
                @php
                    $savings_total = 0;
                    $offers = [];
                    if ($coupon_discount > 0) {
                        $offers[] = ['label' => translate('Coupon').($coupon_code ? ' ('.$coupon_code.')' : ''), 'amount' => $coupon_discount];
                        $savings_total += $coupon_discount;
                    }
                    if (Session::has('club_point') && Session::get('club_point') > 0) {
                        $offers[] = ['label' => translate('Redeem point'), 'amount' => Session::get('club_point')];
                        $savings_total += Session::get('club_point');
                    }
                @endphp
                @if (!empty($offers))
                    <tr>
                        <th class="pl-0 fs-14 pt-0 pb-2 text-dark fw-600 border-top-0 align-top">{{ translate('Offers Applied') }}</th>
                        <td class="text-right pr-0 fs-14 pt-0 pb-2 fw-600 border-top-0">
                            <div class="d-flex flex-column align-items-end">
                                @foreach($offers as $offer)
                                    <span class="badge badge-light border mb-1 px-2 py-1">
                                        {{ $offer['label'] }} <span class="text-danger">-{{ single_price($offer['amount']) }}</span>
                                    </span>
                                @endforeach
                                <span class="text-success fs-13 fw-700">{{ translate('You saved') }} {{ single_price($savings_total) }}</span>
                            </div>
                        </td>
                    </tr>
                @endif
                <!-- Total -->
                <tr class="cart-total">
                    <th class="pl-0 fs-14 text-dark fw-700"><span class="strong-600">{{ translate('Grand Total') }}</span></th>
                    <td class="text-right pr-0 fs-16 fw-800 text-primary">
                        <strong><span>{{ single_price($total) }}</span></strong>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Coupon System -->
        @if (get_setting('coupon_system') == 1)
            @if ($coupon_discount > 0 && $coupon_code)
                <div class="mt-3">
                    <form class="" id="remove-coupon-form" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <div class="form-control">{{ $coupon_code }}</div>
                            <div class="input-group-append">
                                <button type="button" id="coupon-remove"
                                    class="btn btn-primary">{{ translate('Change Coupon') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <div class="mt-3">
                    <form class="" id="apply-coupon-form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="owner_id" value="{{ $carts[0]['owner_id'] }}">
                        <div class="input-group">
                            <input type="text" class="form-control rounded-0" name="code"
                                onkeydown="return event.key != 'Enter';"
                                placeholder="{{ translate('Have coupon code? Apply here') }}" required>
                            <div class="input-group-append">
                                <button type="button" id="coupon-apply"
                                    class="btn btn-primary rounded-0">{{ translate('Apply') }}</button>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1 fs-12">{{ translate('Enter valid coupon. Discounts stack only where allowed.') }}</small>
                    </form>
                </div>
            @endif
        @endif

    </div>
</div>
