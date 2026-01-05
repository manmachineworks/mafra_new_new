@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="aiz-titlebar mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <b class="fs-20 fw-700 text-dark">{{ translate('Wishlist')}}</b>
            </div>
        </div>
    </div>

    @if (count($wishlists) > 0)
        <div class="row row-cols-xxl-5 row-cols-xl-4 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-2 gutters-16 border-top border-left mx-1 mx-md-0 mb-4">
            @foreach($wishlists as $key => $wishlist)
                @php
                    $product = $wishlist->product;
                    if (!$product) {
                        continue;
                    }
                    $product_url = $product->auction_product ? route('auction-product', $product->slug) : route('product', $product->slug);
                    $brand_name = optional($product->brand)->getTranslation('name')
                        ?? optional(optional($product->user)->shop)->name
                        ?? '';
                    $hover_image = null;
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
                    $customLabels = get_custom_labels($product->custom_label_id);
                    $product_stock_total = $product->stocks ? $product->stocks->sum('qty') : $product->current_stock;
                    $cart_added = [];
                    $carts = get_user_cart();
                    if (count($carts) > 0) {
                        $cart_added = $carts->pluck('product_id')->toArray();
                    }
                @endphp
                <div class="col py-3 text-center border-right border-bottom has-transition hov-shadow-out z-1" id="wishlist_{{ $wishlist->id }}">
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
                                <span class="absolute-top-right fs-11 text-white fw-700 px-2 lh-1-6 ml-1 mt-1" style="background-color:#212121;">
                                    {{ $brand_name }}
                                </span>
                            @endif

                            @if ($product_stock_total <= 0)
                                <span class="absolute-top-right fs-11 text-white fw-700 px-2 lh-1-6 ml-1 mt-1" style="background-color:#c70a0a;">
                                    {{ translate('Out of Stock') }}
                                </span>
                            @endif

                            @if (discount_in_percentage($product) > 0)
                                <span class="absolute-top-left ml-1 mt-1 fs-11 fw-700 text-white lh-1-6 w-35px text-center"
                                    style="background-color:#c70a04;">-{{ discount_in_percentage($product) }}%</span>
                            @endif

                            @if ($customLabels)
                                @php $badgeIndex = 1; @endphp
                                @foreach ($customLabels as $keyLabel => $customLabel)
                                    <span class="absolute-top-left rounded rounded-4 fs-11 fw-700 px-2 lh-1-8 ml-1 mt-1"
                                        style="background-color:{{ $customLabel->background_color }};
                                            color:{{ $customLabel->text_color }};
                                            top:{{ 25 * $badgeIndex }}px;">
                                        {{ $customLabel->text }}
                                    </span>
                                    @php $badgeIndex++; @endphp
                                @endforeach
                            @endif

                            <div class="absolute-top-right aiz-p-hov-icon">
                                <a href="javascript:void(0)" class="hov-svg-white round-icon-btn" onclick="removeFromWishlist({{ $wishlist->id }})" data-toggle="tooltip" data-title="{{ translate('Remove from wishlist') }}" data-placement="left">
                                    <i class="la la-trash" style="color: #919199;"></i>
                                </a>
                            </div>

                            <!-- @if ($product->auction_product == 0)
                                <div class="d-sm-none position-absolute aiz-p-hov-icon-mobile"
                                    style="bottom: -10px; left: 50%; transform: translateX(-50%); z-index: 10;">
                                    <div class="d-inline-flex px-2 py-1 shadow-sm">
                                        <a href="javascript:void(0)" class="hov-svg-white d-inline-block mb-2 round-icon-btn"
                                            onclick="showAddToCartModal({{ $product->id }})" data-toggle="tooltip"
                                            data-title="{{ translate('Add to Cart') }}" data-placement="top">
                                            <i class="las la-2x la-shopping-cart"></i>
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
                                @else
                                    <a class="cart-btn absolute-bottom-left w-100 h-35px aiz-p-hov-icon text-white fs-13 fw-700 d-none d-sm-flex flex-column justify-content-center align-items-center @if (in_array($product->id, $cart_added)) active @endif"
                                        href="javascript:void(0)" @if (Auth::check() || get_Setting('guest_checkout_activation') == 1) onclick="addToCartSingleProduct({{ $product->id }})" @else onclick="showLoginModal()" @endif>
                                        <span class="cart-btn-text">
                                            {{ translate('Add to Cart') }}
                                        </span>
                                        <span><i class="las la-2x la-shopping-cart"></i></span>
                                    </a>
                                @endif
                            @endif -->
                        </div>

                        <div class="px-2 px-md-3 text-left">
                            <a href="{{ $product_url }}" class="text-reset d-block mt-1" title="{{ $product->getTranslation('name') }}">
                                <div class="ph-title text-truncate-2">{{ $product->getTranslation('name') }}</div>
                            </a>
                            @if ($product->auction_product == 0)
                                <div class="fs-14 d-flex mt-1">
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
                                </div>

                                <a class="ph-cart-btn cart-btn m-1 w-100 h-35px aiz-p-hov-icon text-white fs-13 fw-700 d-none d-sm-flex flex-column justify-content-center align-items-center @if (in_array($product->id, $cart_added)) active @endif"
                                    href="javascript:void(0)" @if (Auth::check() || get_Setting('guest_checkout_activation') == 1) onclick="addToCartSingleProduct({{ $product->id }})" @else onclick="showLoginModal()" @endif>
                                    <span class="cart-btn-text">
                                        {{ translate('Add to Cart') }}
                                    </span>
                                    <span><i class="las la-2x la-shopping-cart"></i></span>
                                </a>
                            @else
                                <div class="fs-14 mt-1">
                                    <span class="fw-700">{{ single_price($product->starting_bid) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="row">
            <div class="col">
                <div class="text-center bg-white p-4 border">
                    <img class="mw-100 h-200px" src="{{ static_asset('assets/img/nothing.svg') }}" alt="Image">
                    <h5 class="mb-0 h5 mt-3">{{ translate("There isn't anything added yet")}}</h5>
                </div>
            </div>
        </div>
    @endif
    <!-- Pagination -->
    <div class="aiz-pagination">
        {{ $wishlists->links() }}
    </div>
@endsection

@section('modal')
    <!-- add To Cart Modal -->
    <div class="modal fade" id="addToCart" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="c-preloader">
                    <i class="fa fa-spin fa-spinner"></i>
                </div>
                <button type="button" class="close absolute-close-btn" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div id="addToCart-modal-body">

                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        function removeFromWishlist(id){
            $.post('{{ route('wishlists.remove') }}',{_token:'{{ csrf_token() }}', id:id}, function(data){
                $('#wishlist').html(data);
                $('#wishlist_'+id).hide();
                AIZ.plugins.notify('success', '{{ translate("Item has been renoved from wishlist") }}');
            })
        }
    </script>
@endsection
