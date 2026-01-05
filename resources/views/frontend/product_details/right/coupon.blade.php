<div class="card-panel mb-4 coupon-card">
    <div class="section-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-uppercase fw-bold fs-16 mb-0 text-brand">{{ translate('Coupon') }}</p>
            @if($product->user->shop)
                <a href="{{$product->user->shop?->slug != null ? route('shop.visit', $product->user->shop?->slug) : "#"}}"
                   class="text-muted small text-underline">
                    {{ translate('Seller Coupons') }}
                </a>
            @endif
        </div>

        <div class="button-section row mb-3">
            @if(date('Y-m-d H:i:s', time()) < date('Y-m-d H:i:s', $product->preorder_coupon?->coupon_end_date))
                <div class="col-12">
                    <span class="coupon-pill d-flex align-items-center justify-content-between w-100">
                        <span id="coupon-code">{{ $product->preorder_coupon?->coupon_code }}</span>
                        <button id="copy-btn" class="btn btn-link px-2 text-white" onclick="copyCouponCode()" type="button" aria-label="{{ translate('Copy coupon code') }}">
                            <i class="las la-copy"></i>
                        </button>
                    </span>
                </div>
            @else
                <div class="col-12">
                    <span class="coupon-pill disabled d-flex align-items-center justify-content-between w-100">
                        <span id="coupon-code">{{ translate('No Coupon Available') }}</span>
                    </span>
                </div>
            @endif
        </div>

        <div class="text-muted small">
            @if(date('Y-m-d H:i:s', time()) < date('Y-m-d H:i:s', $product->preorder_coupon?->coupon_end_date))
                <p class="mb-2">
                    <i class="las la-check-circle text-brand mr-1"></i>
                    {{ $product->preorder_coupon?->coupon_type == 'flat' ? single_price($product->preorder_coupon?->coupon_amount) : $product->preorder_coupon?->coupon_amount.'% ' }}
                    {{ translate('Coupon discount on Preorder') }}
                </p>
            @endif

            @if($product->preorder_prepayment?->prepayment_amount != null)
                <p class="mb-2">
                    <i class="las la-check-circle text-brand mr-1"></i>
                    {{ translate('Pay only') }} {{ single_price($product->preorder_prepayment?->prepayment_amount) }} {{ translate('to ensure your order') }}
                </p>
            @endif
            @if($product->is_cod)
                <p class="mb-0">
                    <i class="las la-check-circle text-brand mr-1"></i>
                    {{ translate('Cash on delivery available') }}
                </p>
            @endif
        </div>
    </div>
</div>

@section('script')

<script>
    function copyCouponCode() {
        const couponCode = document.getElementById('coupon-code').textContent;
        navigator.clipboard.writeText(couponCode)
            .then(() => {
                AIZ.plugins.notify('success', "{{ translate('Coupon code copied to clipboard!') }}");
            })
            .catch(err => {
                AIZ.plugins.notify('error', "{{ translate('Failed to copy') }}");
            });
    }
</script>
@endsection
