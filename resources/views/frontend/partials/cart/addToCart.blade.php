<div class="modal-body p-0 c-scrollbar-light" id="mm-quickview">
    @php
        $photos = collect(explode(',', (string) $product->photos))->filter()->values();
        $stocks = $product->stocks;
        $totalQty = (int) $stocks->sum('qty');

        $inStock = $product->digital == 1 ? true : ($totalQty > 0);
        $hasDiscount = home_price($product) != home_discounted_price($product);
        $discountPercent = (int) discount_in_percentage($product);

        $approvedReviewsCount = $product->approved_reviews_count
            ?? $product->reviews()->where('status', 1)->count();

        $brandName = optional($product->brand)->name;
        $defaultStock = $stocks->firstWhere('variant', '') ?? $stocks->first();
        $defaultSku = optional($defaultStock)->sku;

        $shortDescriptionHtml = method_exists($product, 'getTranslation')
            ? $product->getTranslation('short_description')
            : ($product->short_description ?? null);
        $shortDescriptionText = trim(strip_tags((string) $shortDescriptionHtml));
        $shortDescriptionText = $shortDescriptionText ? \Illuminate\Support\Str::limit($shortDescriptionText, 240) : null;

        $basePrice = $product->variant_product ? null : (float) home_base_price($product, false);
        $discountedBasePrice = $product->variant_product ? null : (float) home_discounted_base_price($product, false);
        $discountAmount = (!is_null($basePrice) && !is_null($discountedBasePrice)) ? max(0, $basePrice - $discountedBasePrice) : null;
    @endphp

    <div class="p-3 p-md-4 border-bottom bg-white">
        <div class="d-flex align-items-start justify-content-between">
            <div class="minw-0">
                <div class="text-muted fs-12 fw-700">{{ translate('Quick View') }}</div>
                <h4 class="mb-0 fs-16 fw-800 text-truncate" title="{{ $product->getTranslation('name') }}">
                    {{ $product->getTranslation('name') }}
                </h4>
                <div class="d-flex flex-wrap align-items-center mt-2" style="gap: 10px;">
                    @if($brandName)
                        <span class="badge badge-inline badge-light">
                            <i class="las la-tag mr-1" aria-hidden="true"></i>{{ translate('Brand') }}: {{ $brandName }}
                        </span>
                    @endif

                    @if($approvedReviewsCount > 0 && (float) $product->rating > 0)
                        <span class="d-inline-flex align-items-center">
                            <span class="rating mr-1">{!! renderStarRating($product->rating) !!}</span>
                            <span class="text-muted fs-13">({{ $approvedReviewsCount }} {{ translate('reviews') }})</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- <button type="button" class="btn btn-light btn-icon rounded-circle" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                <i class="las la-times" aria-hidden="true"></i>
            </button> -->
        </div>
    </div>

    <div class="p-3 p-md-4">
        <div class="row">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="position-relative">
                    <div>
                        @if($discountPercent > 0)
                            <span class="badge badge-inline text-white ml-1 mt-1" style="background-color:#c70a04;">
                                -{{ $discountPercent }}% {{ translate('OFF') }}
                            </span>
                        @endif
                        @if((int) $product->todays_deal === 1)
                            <span class="badge badge-inline badge-dark text-white ml-1 mt-1" >
                                <i class="las la-bolt mr-1" aria-hidden="true"></i>{{ translate("Today's Deal") }}
                            </span>
                        @endif


                    </div>

                    <div class="row gutters-10 flex-row-reverse">
                        <div class="col">
                            <div class="aiz-carousel product-gallery"
                                data-nav-for=".product-gallery-thumb"
                                data-fade="true"
                                data-auto-height="true">
                                @forelse ($photos as $photo)
                                    <div class="carousel-box img-zoom">
                                        <img class="img-fluid lazyload rounded"
                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            data-src="{{ uploaded_asset($photo) }}"
                                            alt="{{ $product->getTranslation('name') }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            style="width:100%;height:360px;object-fit:cover;">
                                    </div>
                                @empty
                                    <div class="carousel-box img-zoom">
                                        <img class="img-fluid rounded"
                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            alt="{{ $product->getTranslation('name') }}"
                                            style="width:100%;height:360px;object-fit:cover;">
                                    </div>
                                @endforelse

                                @foreach ($stocks as $stock)
                                    @if ($stock->image)
                                        <div class="carousel-box img-zoom">
                                            <img class="img-fluid lazyload rounded"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($stock->image) }}"
                                                alt="{{ $product->getTranslation('name') }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                style="width:100%;height:360px;object-fit:cover;">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="col-auto w-90px">
                            <div class="aiz-carousel carousel-thumb product-gallery-thumb"
                                data-items="5"
                                data-nav-for=".product-gallery"
                                data-vertical="true"
                                data-focus-select="true">
                                @foreach ($photos as $photo)
                                    <div class="carousel-box c-pointer">
                                        <img class="lazyload rounded"
                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            data-src="{{ uploaded_asset($photo) }}"
                                            alt="{{ $product->getTranslation('name') }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            style="width:100%;height:62px;object-fit:cover;">
                                    </div>
                                @endforeach

                                @foreach ($stocks as $stock)
                                    @if ($stock->image)
                                        <div class="carousel-box c-pointer" data-variation="{{ $stock->variant }}">
                                            <img class="lazyload rounded"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($stock->image) }}"
                                                alt="{{ $product->getTranslation('name') }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                style="width:100%;height:62px;object-fit:cover;">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-3" aria-live="polite">
                        @if($product->digital == 1)
                            <span class="badge badge-inline badge-success">{{ translate('In stock') }}</span>
                        @elseif(!$inStock)
                            <span class="badge badge-inline badge-danger">{{ translate('Out of Stock') }}</span>
                        @elseif($product->stock_visibility_state == 'quantity')
                            <span class="badge badge-inline badge-primary">
                                {{ translate('Available') }}: <b id="available-quantity">{{ $totalQty }}</b>
                            </span>
                        @elseif($product->stock_visibility_state == 'text')
                            <span class="badge badge-inline badge-success">
                                <b id="available-quantity">{{ translate('In Stock') }}</b>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="bg-white border rounded p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-end justify-content-between" style="gap: 12px;">
                        <div>
                            <div class="text-muted fs-12 fw-700">{{ translate('Pricing') }}</div>
                            <div class="d-flex flex-wrap align-items-baseline" style="gap: 10px;">
                                <div class="fs-22 fw-800" style="color: var(--primary);">
                                    {{ home_discounted_price($product) }}/- 
                                </div>
                                @if($hasDiscount)
                                    <del class="text-muted">{{ home_price($product) }}/- </del>
                                @else
                                
                                    <del class="text-muted">{{ purchase_price($product) }}/- </del>
                               
                                @endif
                                @if($product->unit)
                                    <span class="text-muted fs-12">/{{ $product->getTranslation('unit') }}</span>
                                @endif
                            </div>

                            @if($hasDiscount)
                                <div class="mt-2 d-flex flex-wrap" style="gap: 8px;">
                                    @if($discountPercent > 0)
                                        <span class="badge badge-inline badge-light">
                                            <i class="las la-tags mr-1" aria-hidden="true"></i>{{ translate('Save') }} {{ $discountPercent }}%
                                        </span>
                                    @endif
                                    @if(!$product->variant_product && !is_null($discountAmount) && $discountAmount > 0)
                                        <span class="badge badge-inline badge-light">
                                            <i class="las la-coins mr-1" aria-hidden="true"></i>{{ translate('Save') }} {{ format_price($discountAmount) }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if (addon_is_activated('club_point') && (int) $product->earn_point > 0)
                            <div class="badge badge-inline badge-dark">
                                <i class="las la-gem mr-1" aria-hidden="true"></i>{{ translate('Club Point') }}: <b>{{ $product->earn_point }}</b>
                            </div>
                        @endif
                    </div>

                

                    @if ($product->getTranslation('short_description'))
                    <div class="mt-3 no-gutters">
                            <div class="text-muted fs-12 fw-700">{{ translate('About this product') }}</div>
                            <div class="mb-0 mt-1 text-dark">
                                {!! $product->getTranslation('short_description') !!}
                            </div>
                        </div>
                @endif

                    <!-- <div class="mt-3">
                        <div class="text-muted fs-12 fw-700">{{ translate('Highlights') }}</div>
                        <div class="row mt-2">
                            @if($defaultSku)
                                <div class="col-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="las la-barcode mr-2" aria-hidden="true"></i>
                                        <div class="minw-0">
                                            <div class="text-muted fs-11">{{ translate('SKU') }}</div>
                                            <div class="fw-700 text-truncate">{{ $defaultSku }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="las la-box mr-2" aria-hidden="true"></i>
                                    <div class="minw-0">
                                        <div class="text-muted fs-11">{{ translate('Min. Qty') }}</div>
                                        <div class="fw-700">{{ (int) $product->min_qty }}</div>
                                    </div>
                                </div>
                            </div>
                            @if($product->est_shipping_days)
                                <div class="col-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="las la-shipping-fast mr-2" aria-hidden="true"></i>
                                        <div class="minw-0">
                                            <div class="text-muted fs-11">{{ translate('Est. Shipping') }}</div>
                                            <div class="fw-700">{{ $product->est_shipping_days }} {{ translate('Days') }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div> -->

                    <form id="option-choice-form" class="mt-3" aria-label="{{ translate('Select product options') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $product->id }}">

                        @if($product->digital != 1)
                            @if ($product->choice_options)
                                @foreach (json_decode($product->choice_options) as $choice)
                                    <div class="mt-3">
                                        <div class="fs-13 fw-700 text-dark mb-2">{{ get_single_attribute_name($choice->attribute_id) }}</div>
                                        <div class="aiz-radio-inline" role="radiogroup">
                                            @foreach ($choice->values as $k => $value)
                                                <label class="aiz-megabox pl-0 mr-2 mb-2">
                                                    <input type="radio"
                                                        name="attribute_id_{{ $choice->attribute_id }}"
                                                        value="{{ $value }}"
                                                        @if($k == 0) checked @endif>
                                                    <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center px-3 py-2">
                                                        {{ $value }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if ($product->colors && count(json_decode($product->colors)) > 0)
                                <div class="mt-3">
                                    <div class="fs-13 fw-700 text-dark mb-2">{{ translate('Color') }}</div>
                                    <div class="aiz-radio-inline" role="radiogroup">
                                        @foreach (json_decode($product->colors) as $k => $color)
                                            <label class="aiz-megabox pl-0 mr-2 mb-2"
                                                data-toggle="tooltip"
                                                data-title="{{ get_single_color_name($color) }}">
                                                <input type="radio"
                                                    name="color"
                                                    value="{{ get_single_color_name($color) }}"
                                                    @if($k == 0) checked @endif>
                                                <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-2">
                                                    <span class="size-25px d-inline-block rounded" style="background: {{ $color }};"></span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mt-3">
                                <div class="fs-13 fw-700 text-dark mb-2">{{ translate('Quantity') }}</div>
                                <div class="row no-gutters align-items-center aiz-plus-minus" style="width: 160px;">
                                    <button class="btn col-auto btn-icon btn-sm btn-light" type="button" data-type="minus" data-field="quantity" aria-label="{{ translate('Decrease quantity') }}" disabled>
                                        <i class="las la-minus" aria-hidden="true"></i>
                                    </button>
                                    <label class="sr-only" for="mm-qty-input">{{ translate('Quantity') }}</label>
                                    <input id="mm-qty-input" type="number" name="quantity"
                                        class="col border-0 text-center flex-grow-1 fs-16 input-number"
                                        value="{{ (int) $product->min_qty }}"
                                        min="{{ (int) $product->min_qty }}"
                                        max="{{ max((int) $product->min_qty, $totalQty) }}"
                                        inputmode="numeric" lang="en">
                                    <button class="btn col-auto btn-icon btn-sm btn-light" type="button" data-type="plus" data-field="quantity" aria-label="{{ translate('Increase quantity') }}">
                                        <i class="las la-plus" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="quantity" value="1">
                        @endif

                        <div class="row no-gutters pt-3 d-none" id="chosen_price_div" aria-live="polite">
                            <div class="col-12">
                                <div class="text-muted fs-12 fw-700">{{ translate('Total Price') }}</div>
                                <div class="fs-20 fw-800" id="chosen_price" style="color: var(--primary);"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="p-3 p-md-4 border-top bg-white d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px; position: sticky; bottom: 0; z-index: 10;">
        <div>
            <div class="text-muted fs-12 fw-700 mb-1">{{ translate('Total') }}</div>
            <div class="fs-18 fw-800" style="color: var(--primary);">{{ home_discounted_price($product) }}</div>
        </div>

        <div class="d-flex flex-wrap" style="gap: 10px;">
            @if ($product->digital == 1)
                <button type="button" class="btn btn-primary add-to-cart"
                    @if (Auth::check() || get_setting('guest_checkout_activation') == 1) onclick="addToCart()" @else onclick="showLoginModal()" @endif>
                    <i class="las la-shopping-cart mr-1" aria-hidden="true"></i>{{ translate('Add to cart') }}
                </button>
            @elseif ($product->external_link)
                <a class="btn btn-outline-secondary" href="{{ $product->external_link }}" rel="noopener">
                    <i class="las la-external-link-alt mr-1" aria-hidden="true"></i>{{ translate($product->external_link_btn) }}
                </a>
            @else
                <button type="button" class="btn btn-primary add-to-cart @if(!$inStock) d-none @endif"
                    @if (Auth::check() || get_setting('guest_checkout_activation') == 1) onclick="addToCart()" @else onclick="showLoginModal()" @endif>
                    <i class="las la-shopping-cart mr-1" aria-hidden="true"></i>{{ translate('Add to cart') }}
                </button>
                <button type="button" class="btn btn-outline-secondary out-of-stock @if($inStock) d-none @endif" disabled aria-disabled="true">
                    <i class="las la-cart-arrow-down mr-1" aria-hidden="true"></i>{{ translate('Out of Stock') }}
                </button>
            @endif
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document)
        .off('change.mmAddToCart', '#option-choice-form input')
        .on('change.mmAddToCart', '#option-choice-form input', function () {
            if (typeof getVariantPrice === 'function') {
                getVariantPrice();
            }
        });
</script>