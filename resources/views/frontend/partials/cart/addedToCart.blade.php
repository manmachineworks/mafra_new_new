<div class="modal-body p-0 c-scrollbar-light">

    {{-- Top success header --}}
    <div class="p-4 p-md-5 border-bottom bg-light">
        <div class="d-flex align-items-start justify-content-between">
            <div class="d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                     style="width:44px;height:44px;background:rgba(133,181,103,.15);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 36 36">
                        <g transform="translate(-6269 7766)">
                            <path d="M12.8,32.8a3.6,3.6,0,1,0,3.6,3.6A3.584,3.584,0,0,0,12.8,32.8ZM2,4V7.6H5.6l6.471,13.653-2.43,4.41A3.659,3.659,0,0,0,9.2,27.4,3.6,3.6,0,0,0,12.8,31H34.4V27.4H13.565a.446.446,0,0,1-.45-.45.428.428,0,0,1,.054-.216L14.78,23.8H28.19a3.612,3.612,0,0,0,3.15-1.854l6.435-11.682A1.74,1.74,0,0,0,38,9.4a1.8,1.8,0,0,0-1.8-1.8H9.587L7.877,4H2ZM30.8,32.8a3.6,3.6,0,1,0,3.6,3.6A3.584,3.584,0,0,0,30.8,32.8Z"
                                  transform="translate(6267 -7770)" fill="#85b567"/>
                            <rect width="9" height="3" rx="1.5" transform="translate(6284.343 -7757.879) rotate(45)" fill="#fff"/>
                            <rect width="3" height="13" rx="1.5" transform="translate(6295.657 -7760.707) rotate(45)" fill="#fff"/>
                        </g>
                    </svg>
                </div>

                <div>
                    <div class="text-success fw-700" style="font-size:18px;">
                        {{ translate('Added to cart') }}
                    </div>
                    <div class="text-secondary" style="font-size:13px;">
                        {{ translate('You can checkout now or continue shopping.') }}
                    </div>
                </div>
            </div>

            <!-- <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button> -->
        </div>
    </div>

    <div class="p-4 p-md-5">

        {{-- Product card --}}
        <div class="border rounded-lg p-3 p-md-4 mb-4 bg-white">
            <div class="d-flex">
                <a class="mr-3 flex-shrink-0 d-block" href="{{ route('product', $product->slug) }}">
                    <img
                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                        data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                        class="lazyload img-fit"
                        alt="Product Image"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                        style="width:92px;height:92px;border-radius:12px;object-fit:cover;">
                </a>

                <div class="flex-grow-1 minw-0">
                    <h6 class="mb-1 fw-700 text-dark text-truncate-2" style="font-size:14px;">
                        <a href="{{ route('product', $product->slug) }}" class="text-reset">
                            {{ $product->getTranslation('name') }}
                        </a>
                    </h6>

                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <div class="text-secondary" style="font-size:13px;">
                            {{ translate('Total') }}
                            <span class="d-block text-dark fw-700" style="font-size:16px;">
                                {{ single_price(cart_product_price($cart, $product, false) * $cart->quantity) }}/- 
                                @php $ppTotal = ($cart->purchase_price ?? $product->purchase_price ?? 0) * $cart->quantity; @endphp
                                @if($ppTotal > 0)
                                    <del class="text-secondary" style="font-size:13px;">{{ single_price($ppTotal) }}/-</del>
                                @endif
                            </span>
                            
                        </div>

                        <div class="text-right" style="font-size:13px;">
                            <div class="text-secondary">{{ translate('Qty') }}</div>
                            <div class="fw-700 text-dark">{{ $cart->quantity }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Frequently bought together --}}
        <div class="border rounded-lg bg-white overflow-hidden">
            <div class="px-3 px-md-4 py-3 border-bottom d-flex align-items-center justify-content-between">
                <h3 class="fs-16 fw-700 mb-0 text-dark">
                    {{ translate('Frequently Bought Together') }}
                </h3>
                <span class="text-secondary" style="font-size:13px;">
                    {{ translate('You may also like') }}
                </span>
            </div>

            <div class="p-3 p-md-4">
                <div class="aiz-carousel gutters-10 half-outside-arrow"
                     data-items="2" data-xl-items="3" data-lg-items="4" data-md-items="3"
                     data-sm-items="2" data-xs-items="2"
                     data-arrows="true" data-infinite="true">

                    @foreach (get_frequently_bought_products($product) as $key => $related_product)
                        <div class="carousel-box">
                            <div class="border rounded-lg bg-white has-transition hov-shadow-md"
                                 style="overflow:hidden;">
                                <a href="{{ route('product', $related_product->slug) }}" class="d-block bg-light">
                                    <img class="img-fit lazyload mx-auto has-transition"
                                         src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                         data-src="{{ uploaded_asset($related_product->thumbnail_img) }}"
                                         alt="{{ $related_product->getTranslation('name') }}"
                                         onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                         style="width:100%;height:150px;object-fit:cover;">
                                </a>

                                <div class="p-2 p-md-3 text-center">
                                    <div class="text-truncate-2 lh-1-4 mb-2"
                                         style="min-height:38px;font-size:13px;">
                                        <a href="{{ route('product', $related_product->slug) }}"
                                           class="text-reset fw-600 hov-text-primary">
                                            {{ $related_product->getTranslation('name') }}
                                        </a>
                                    </div>

                                    <div class="fs-14">
                                        <span class="fw-700 text-primary">{{ home_discounted_base_price($related_product) }}</span>
                                        @if(home_base_price($related_product) != home_discounted_base_price($related_product))
                                            <del class="fw-600 opacity-50 ml-1">{{ home_base_price($related_product) }}</del>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

    </div>

    {{-- Sticky bottom actions --}}
    <div class="p-3 p-md-4 border-top bg-white"
         style="position:sticky;bottom:0;z-index:5;">
        <div class="row gutters-10">
            <div class="col-sm-6">
                <button class="btn btn-outline-secondary btn-block rounded-lg"
                        data-dismiss="modal">
                    {{ translate('Continue Shopping') }}
                </button>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('cart') }}"
                   class="btn btn-primary btn-block rounded-lg fw-700">
                    {{ translate('Checkout') }}
                </a>
            </div>
        </div>
    </div>

</div>
<style>
    .text-truncate-2{
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
}

.hov-shadow-md:hover{
  box-shadow: 0 10px 24px rgba(0,0,0,.10) !important;
  transform: translateY(-2px);
}

.rounded-lg{ border-radius: 14px !important; }
.minw-0{ min-width:0; }

</style>
