@php
    $product_url = $product->auction_product ? route('auction-product', $product->slug) : route('product', $product->slug);
    
    $brand_name = optional($product->brand)->getTranslation('name')
        ?? optional(optional($product->user)->shop)->name
        ?? '';

    $discount_percentage = discount_in_percentage($product);
    $badgeIndex = 0;
    $cart_added = [];
    $hover_image = null;
    $customLabels = get_custom_labels($product->custom_label_id);

    $carts = get_user_cart();
    if (count($carts) > 0) {
        $cart_added = $carts->pluck('product_id')->toArray();
    }

    if (!empty($product->photos)) {
        $photo_array = array_filter(explode(',', $product->photos));
        if (count($photo_array) > 1) {
            $hover_image = get_image($photo_array[1]);
        } elseif (count($photo_array) === 1) {
            $hover_image = get_image($photo_array[0]);
        }
    }

    $colors = is_string($product->colors) ? json_decode($product->colors, true) : $product->colors;
    $attributes = is_string($product->attributes) ? json_decode($product->attributes, true) : $product->attributes;
@endphp

<div class="aiz-card-box h-auto bg-white py-3 mt-1 hov-scale-img product-card @if ($hover_image) has-hover-img @endif">
   <div class="position-relative h-140px h-md-200px img-fit overflow-hidden">
        <a href="{{ $product_url }}" class="d-block h-100 position-relative image-hover-effect">
            <img class="lazyload mx-auto img-fit has-transition product-card-img product-card-img--main"
                src="{{ get_image($product->thumbnail) }}"
                alt="{{ $product->getTranslation('name') }}"
                title="{{ $product->getTranslation('name') }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
            @if ($hover_image)
                <img class="lazyload mx-auto img-fit has-transition product-card-img product-card-img--hover"
                    src="{{ $hover_image }}"
                    alt="{{ $product->getTranslation('name') }}"
                    title="{{ $product->getTranslation('name') }}"
                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
            @endif
        </a>

         @if(!empty($brand_name))
            <!-- Brand name tag -->
            <span class="absolute-top-right fs-11 text-white fw-700 px-2 lh-1-6 ml-1 mt-1"
                style="background-color:#212121;">
                {{ $brand_name }}
            </span>
        @endif
        
         @php
            $product_stock_total = $product->stocks ? $product->stocks->sum('qty') : $product->current_stock;
        @endphp
        @if ($product_stock_total <= 0)
            <span class="absolute-top-right fs-11 text-white fw-700 px-2 lh-1-6 ml-1 mt-1" style="background-color:#c70a0a;">
                {{ translate('Out of Stock') }}
            </span>
        @endif

         <!-- Discount / stock badges -->
        @if (discount_in_percentage($product) > 0)
            <span class="absolute-top-left ml-1 mt-1 fs-11 fw-700 text-white lh-1-6 w-35px text-center"
                style="background-color:#c70a04;">-{{ discount_in_percentage($product) }}%</span>
        @endif
       

        <!-- @if ($discount_percentage > 0)
            <span class="absolute-top-left rounded rounded-4 bg-primary ml-1 mt-1 fs-11 fw-700 text-white w-35px text-center"
                style="padding-top:2px; padding-bottom:2px; top:{{ 25 * $badgeIndex }}px;">
                -{{ $discount_percentage }}%
            </span>
            @php $badgeIndex++; @endphp
        @endif

        @if ($product->wholesale_product)
            <span class="absolute-top-left rounded rounded-4 fs-11 text-white fw-700 px-2 lh-1-8 ml-1 mt-1"
                style="background-color:#455a64; top:{{ 25 * $badgeIndex }}px;">
                {{ translate('Wholesale') }}
            </span>
            @php $badgeIndex++; @endphp
        @endif -->

        @if ($customLabels)
            @foreach ($customLabels as $key => $customLabel)
                <span class="absolute-top-left rounded rounded-4 fs-11 fw-700 px-2 lh-1-8 ml-1 mt-1"
                    style="background-color:{{ $customLabel->background_color }};
                        color:{{ $customLabel->text_color }};
                        top:{{ 25 * $badgeIndex }}px;">
                    {{ $customLabel->text }}
                </span>
                @php $badgeIndex++; @endphp
            @endforeach
        @endif

        @if ($product->auction_product == 0)
            <div class="d-none d-sm-block absolute-top-right aiz-p-hov-icon">
                <a href="javascript:void(0)" class="hov-svg-white round-icon-btn"
                   onclick="addToWishList({{ $product->id }})"
                   data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.4" viewBox="0 0 16 14.4">
                        <g transform="translate(-3.05 -4.178)">
                            <path d="M11.3,5.507l-.247.246L10.8,5.506A4.538,4.538,0,1,0,4.38,11.919l.247.247,6.422,6.412,6.422-6.412.247-.247A4.538,4.538,0,1,0,11.3,5.507Z"
                                  transform="translate(0 0)" fill="#919199" />
                            <path d="M11.3,5.507l-.247.246L10.8,5.506A4.538,4.538,0,1,0,4.38,11.919l.247.247,6.422,6.412,6.422-6.412.247-.247A4.538,4.538,0,1,0,11.3,5.507Z"
                                  transform="translate(0 0)" fill="#919199" />
                        </g>
                    </svg>
                </a>

                <a href="javascript:void(0)" class="hov-svg-white round-icon-btn"
                   onclick="addToCompare({{ $product->id }})"
                   data-toggle="tooltip" data-title="{{ translate('Add to compare') }}" data-placement="left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M18.037,5.547v.8a.8.8,0,0,1-.8.8H7.221a.4.4,0,0,0-.4.4V9.216a.642.642,0,0,1-1.1.454L2.456,6.4a.643.643,0,0,1,0-.909L5.723,2.227a.642.642,0,0,1,1.1.454V4.342a.4.4,0,0,0,.4.4H17.234a.8.8,0,0,1,.8.8Zm-3.685,4.86a.642.642,0,0,0-1.1.454v1.661a.4.4,0,0,1-.4.4H2.84a.8.8,0,0,0-.8.8v.8a.8.8,0,0,0,.8.8H12.854a.4.4,0,0,1,.4.4V17.4a.642.642,0,0,0,1.1.454l3.267-3.268a.643.643,0,0,0,0-.909Z"
                              transform="translate(-2.037 -2.038)" fill="#919199" />
                    </svg>
                </a>

                  <a href="javascript:void(0)" class="hov-svg-white round-icon-btn"
                   onclick="showAddToCartModal({{ $product->id }})"
                   data-toggle="tooltip" data-title="{{ translate('Quick View') }}" data-placement="left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M8.006,4.667A3.333,3.333,0,1,0,11.339,8,3.333,3.333,0,0,0,8.006,4.667Zm0,5.5A2.167,2.167,0,1,1,10.173,7,2.167,2.167,0,0,1,8.006,10.167ZM15.362,7.551C14.286,5.6,11.9,3.667,8.006,3.667S1.726,5.6.65,7.551a1.6,1.6,0,0,0,0,1.9C1.726,10.4,4.112,12.333,8.006,12.333s6.28-1.933,7.356-3.882A1.6,1.6,0,0,0,15.362,7.551Zm-6.2,3.882a5.978,5.978,0,0,1-5.162-2.882A5.978,5.978,0,0,1,9.162,5a5.978,5.978,0,0,1,5.162,2.882A5.978,5.978,0,0,1,9.162,11.433Z"
                              transform="translate(0 0)" fill="#919199" />
                    </svg>
                </a>
            </div>

            <div class="d-sm-none position-absolute aiz-p-hov-icon-mobile"
                style="bottom: -10px; left: 50%; transform: translateX(-50%); z-index: 10;">
                <div class="d-inline-flex px-2 py-1 shadow-sm">
                     <a href="javascript:void(0)" class="hov-svg-white d-inline-block mb-2 round-icon-btn"
                        onclick="showAddToCartModal({{ $product->id }})" data-toggle="tooltip"
                        data-title="{{ translate('Add to Cart') }}" data-placement="top">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                            <path
                                d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                                fill="#919199" />
                        </svg>
                    </a> 

                     <a href="javascript:void(0)" class="hov-svg-white round-icon-btn"
                    onclick="addToWishList({{ $product->id }})"
                    data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.4" viewBox="0 0 16 14.4">
                            <g transform="translate(-3.05 -4.178)">
                                <path d="M11.3,5.507l-.247.246L10.8,5.506A4.538,4.538,0,1,0,4.38,11.919l.247.247,6.422,6.412,6.422-6.412.247-.247A4.538,4.538,0,1,0,11.3,5.507Z"
                                    transform="translate(0 0)" fill="#919199" />
                                <path d="M11.3,5.507l-.247.246L10.8,5.506A4.538,4.538,0,1,0,4.38,11.919l.247.247,6.422,6.412,6.422-6.412.247-.247A4.538,4.538,0,1,0,11.3,5.507Z"
                                    transform="translate(0 0)" fill="#919199" />
                            </g>
                        </svg>
                    </a>

                    <a href="javascript:void(0)" class="hov-svg-white round-icon-btn"
                    onclick="addToCompare({{ $product->id }})"
                    data-toggle="tooltip" data-title="{{ translate('Add to compare') }}" data-placement="left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                            <path d="M18.037,5.547v.8a.8.8,0,0,1-.8.8H7.221a.4.4,0,0,0-.4.4V9.216a.642.642,0,0,1-1.1.454L2.456,6.4a.643.643,0,0,1,0-.909L5.723,2.227a.642.642,0,0,1,1.1.454V4.342a.4.4,0,0,0,.4.4H17.234a.8.8,0,0,1,.8.8Zm-3.685,4.86a.642.642,0,0,0-1.1.454v1.661a.4.4,0,0,1-.4.4H2.84a.8.8,0,0,0-.8.8v.8a.8.8,0,0,0,.8.8H12.854a.4.4,0,0,1,.4.4V17.4a.642.642,0,0,0,1.1.454l3.267-3.268a.643.643,0,0,0,0-.909Z"
                                transform="translate(-2.037 -2.038)" fill="#919199" />
                        </svg>
                    </a>

                    <a href="javascript:void(0)" class="hov-svg-white round-icon-btn"
                    onclick="showAddToCartModal({{ $product->id }})"
                    data-toggle="tooltip" data-title="{{ translate('Quick View') }}" data-placement="left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                            <path d="M8.006,4.667A3.333,3.333,0,1,0,11.339,8,3.333,3.333,0,0,0,8.006,4.667Zm0,5.5A2.167,2.167,0,1,1,10.173,7,2.167,2.167,0,0,1,8.006,10.167ZM15.362,7.551C14.286,5.6,11.9,3.667,8.006,3.667S1.726,5.6.65,7.551a1.6,1.6,0,0,0,0,1.9C1.726,10.4,4.112,12.333,8.006,12.333s6.28-1.933,7.356-3.882A1.6,1.6,0,0,0,15.362,7.551Zm-6.2,3.882a5.978,5.978,0,0,1-5.162-2.882A5.978,5.978,0,0,1,9.162,5a5.978,5.978,0,0,1,5.162,2.882A5.978,5.978,0,0,1,9.162,11.433Z"
                                transform="translate(0 0)" fill="#919199" />
                        </svg>
                    </a>
                </div>
            </div>

            @if ((is_array($colors) && count($colors) > 0) || (is_array($attributes) && count($attributes) > 0))
                <a class="cart-btn absolute-bottom-left w-100 h-35px aiz-p-hov-icon text-white fs-13 fw-700 d-none d-sm-flex flex-column justify-content-center align-items-center @if (in_array($product->id, $cart_added)) active @endif"
                    href="javascript:void(0)" onclick="showAddToCartModal({{ $product->id }})">
                    <span class="cart-btn-text">
                        {{ translate('Select Option') }}
                    </span>
                    <span><i class="las la-sliders-h" style="font-size: 1.4rem;"></i></span>
                </a>
            
            @endif
        @endif

        <!-- @if (
            $product->auction_product == 1 &&
                $product->auction_start_date <= strtotime('now') &&
                $product->auction_end_date >= strtotime('now'))
            @php
                $highest_bid = $product->bids->max('amount');
                $min_bid_amount = $highest_bid != null ? $highest_bid + 1 : $product->starting_bid;
            @endphp
            <a class="cart-btn absolute-bottom-left w-100 h-35px aiz-p-hov-icon text-white fs-13 fw-700 d-flex flex-column justify-content-center align-items-center @if (in_array($product->id, $cart_added)) active @endif"
                href="javascript:void(0)" onclick="bid_single_modal({{ $product->id }}, {{ $min_bid_amount }})">
                <span class="cart-btn-text">{{ translate('Place Bid') }}</span>
                <span><i class="las la-2x la-gavel"></i></span>
            </a>
        @endif -->
    </div>

    <div class="px-2 px-md-3 text-left">
       <a href="{{ $product_url }}" class="text-reset d-block mt-1" title="{{ $product->getTranslation('name') }}">
            <div class="ph-title text-truncate-2">{{ $product->getTranslation('name') }}</div>
        </a>
         @if ($product->auction_product == 0)
        <div class="fs-14 d-flex mt-1">
           
                <!-- price -->
                <div class="">
                    <span class="fw-700 text-primary">{{ home_discounted_base_price($product) }}/- </span>
                </div>
                @if (home_base_price($product) != home_discounted_base_price($product))
                    <div class="">
                        <del class="fw-400 text-secondary mr-1">{{ home_base_price($product) }}/- </del>
                    </div>
                @else
                        <div class="">
                            <del class="fw-400 text-secondary ml-1">{{ purchase_price($product) }}/- </del>
                        </div>
                @endif
            @else
                <div class="">
                    <span class="fw-700">{{ single_price($product->starting_bid) }}</span>
                </div>
            @endif
        </div>

               <a class="ph-cart-btn cart-btn m-1 w-100 h-35px aiz-p-hov-icon text-white fs-13 fw-700 d-none d-sm-flex flex-column justify-content-center align-items-center @if (in_array($product->id, $cart_added)) active @endif"
                    href="javascript:void(0)" @if (Auth::check() || get_Setting('guest_checkout_activation') == 1) onclick="addToCartSingleProduct({{ $product->id }})" @else onclick="showLoginModal()" @endif>
                    <span class="cart-btn-text">
                        {{ translate('Add to Cart') }}
                    </span>
                    <span><i class="las la-2x la-shopping-cart"></i></span>
                </a>
        
    </div>
</div>
