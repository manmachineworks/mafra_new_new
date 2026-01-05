@extends('frontend.layouts.app')

@section('meta_title'){{ $detailedProduct->meta_title }}@stop

@section('meta_description'){{ $detailedProduct->meta_description }}@stop

@section('meta_keywords'){{ $detailedProduct->tags }},{{ $detailedProduct->meta_keywords }}@stop

@section('meta')
    @php
        $availability = "out of stock";
        $qty = 0;
        if($detailedProduct->variant_product) {
            foreach ($detailedProduct->stocks as $key => $stock) {
                $qty += $stock->qty;
            }
        }
        else {
            $qty = optional($detailedProduct->stocks->first())->qty;
        }
        if($qty > 0){
            $availability = "in stock";
        }
    @endphp
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $detailedProduct->meta_title }}">
    <meta itemprop="description" content="{{ $detailedProduct->meta_description }}">
    <meta itemprop="image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $detailedProduct->meta_title }}">
    <meta name="twitter:description" content="{{ $detailedProduct->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">
    <meta name="twitter:data1" content="{{ single_price($detailedProduct->unit_price) }}">
    <meta name="twitter:label1" content="Price">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $detailedProduct->meta_title }}" />
    <meta property="og:type" content="og:product" />
    <meta property="og:url" content="{{ route('product', $detailedProduct->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}" />
    <meta property="og:description" content="{{ $detailedProduct->meta_description }}" />
    <meta property="og:site_name" content="{{ get_setting('meta_title') }}" />
    <meta property="og:price:amount" content="{{ single_price($detailedProduct->unit_price) }}" />
    <meta property="product:brand" content="{{ $detailedProduct->brand ? $detailedProduct->brand->name : env('APP_NAME') }}">
    <meta property="product:availability" content="{{ $availability }}">
    <meta property="product:condition" content="new">
    <meta property="product:price:amount" content="{{ number_format($detailedProduct->unit_price, 2) }}">
    <meta property="product:retailer_item_id" content="{{ $detailedProduct->slug }}">
    <meta property="product:price:currency"
        content="{{ get_system_default_currency()->code }}" />
    <meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}">
@endsection

@section('content')
<div class="product-page-modern">
    <style>
        :root {
            --brand-primary: #c70a04;
            --brand-dark: #212121;
            --brand-light: #ffffff;
            --brand-muted: #f5f5f7;
            --brand-border: #e5e7eb;
            --brand-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
        }
        .product-page-modern {
            color: var(--brand-dark);
        }
       
        .product-page-modern .btn-brand,
        .product-page-modern .btn-primary,
        .product-page-modern .btn-dark {
            background: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
            color: #fff !important;
            box-shadow: 0 8px 18px rgba(199, 10, 4, 0.25);
        }
       
        .product-page-modern .btn-outline-brand {
            border: 1px solid var(--brand-primary);
            color: var(--brand-primary);
            background: transparent;
        }
        .product-page-modern .btn-outline-brand:hover {
            background: var(--brand-primary);
            color: #fff;
        }
        .product-page-modern .card-panel {
            background: var(--brand-light);
            border: 1px solid var(--brand-border);
            border-radius: 14px;
            padding: 16px;
            box-shadow: var(--brand-shadow);
        }
        .product-page-modern .status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid transparent;
            background: var(--brand-muted);
            color: var(--brand-dark);
        }
        .product-page-modern .status-pill.success { background: rgba(49, 196, 141, 0.12); color: #1c8a5a; border-color: rgba(49, 196, 141, 0.2); }
        .product-page-modern .status-pill.warning { background: rgba(255, 166, 0, 0.12); color: #a46405; border-color: rgba(255, 166, 0, 0.2); }
        .product-page-modern .status-pill.info { background: rgba(32, 117, 243, 0.12); color: #205fbe; border-color: rgba(32, 117, 243, 0.2); }
        .product-page-modern .coupon-pill {
            display: inline-flex;
            align-items: center;
            background: var(--brand-primary);
            color: #fff;
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 700;
        }
        .product-page-modern .coupon-pill.disabled {
            background: #9ca3af;
        }
        .product-page-modern .shop-card .badge-success-soft,
        .product-page-modern .shop-card .badge-warning-soft {
            padding: 6px 10px;
            border-radius: 10px;
            background: var(--brand-muted);
        }
        .product-page-modern .brand-logo img {
            max-width: 63px;
            max-height: 42px;
            object-fit: contain;
        }
        .product-page-modern .offer-stack .status-pill { width: 100%; border-radius: 12px; }
        .product-page-modern .icon-card svg { opacity: 0.9; }
        .product-page-modern .aiz-carousel .slick-arrow {
            background: rgba(0,0,0,0.55);
            border-radius: 50%;
        }
        .product-page-modern .product-gallery-thumb img {
            border-radius: 10px;
            border: 1px solid var(--brand-border);
        }
        .product-page-modern .product-gallery-thumb .slick-current img {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 2px rgba(199,10,4,0.2);
        }
        .product-page-modern .nav-tabs .nav-link.active,
        .product-page-modern .nav-tabs .nav-item.show .nav-link {
            color: var(--brand-primary);
            border-color: var(--brand-primary) var(--brand-primary) #fff;
        }
        .product-page-modern .nav-tabs .nav-link:hover {
            color: var(--brand-primary);
        }
        .product-page-modern .badge-primary,
        .product-page-modern .bg-primary {
            background: var(--brand-primary) !important;
        }
        .product-page-modern .modal-content,
        .product-modal .modal-content {
            border-radius: 14px;
            border: 1px solid var(--brand-border);
            box-shadow: var(--brand-shadow);
        }
        .product-page-modern .modal-header,
        .product-modal .modal-header {
            border-bottom: 1px solid var(--brand-border);
            background: var(--brand-muted);
        }
        .product-page-modern .modal-footer,
        .product-modal .modal-footer {
            border-top: 1px solid var(--brand-border);
        }
        .product-page-modern .product-quantity .btn-light {
            background: var(--brand-muted);
            border-color: var(--brand-border);
        }
        .product-page-modern .aiz-plus-minus .input-number {
            background: #fff;
            border: 1px solid var(--brand-border);
        }
        .product-page-modern .badge-success { background: #1c8a5a; }
        
    </style>
    <section class="mb-4 pt-3">
        <div class="container">
            <div class="bg-white py-3">
                <div class="row">
                    <!-- Product Image Gallery -->
                    <div class="col-xl-5 col-lg-6 mb-4">
                        @include('frontend.product_details.image_gallery')
                    </div>

                    <!-- Product Details -->
                    <div class="col-xl-7 col-lg-6">
                        @include('frontend.product_details.details')
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="container">
            @if ($detailedProduct->auction_product)
               
                
                <!-- Description, Video, Downloads -->
                @include('frontend.product_details.description')
                 <!-- Reviews & Ratings -->
                @include('frontend.product_details.review_section')
                <!-- Product Query -->
                @include('frontend.product_details.product_queries')
            @else
                <div class="row gutters-16">

                   @php
                       // Provide the right-hand partials with the product instance they expect
                       $product = $detailedProduct;
                   @endphp

                   <!-- Product Image Gallery -->
                <div class="col-md-8 mb-4">
                    @include('frontend.product_details.description')
                    @include('frontend.product_details.review_section')
                    @include('frontend.product_details.frequently_bought_products')
                    @include('frontend.product_details.product_queries')
                    <div class="d-lg-none">
                        @include('frontend.product_details.top_selling_products')
                    </div>
                </div>
                <div class="col-md-4">
                     <!-- offer   -->
                    @include('frontend.product_details.right.offer')
                    <!-- Coupon    -->
                    @if($product->is_coupon)
                    @include('frontend.product_details.right.coupon')
                    @endif
                    <!-- brand   -->
                    @include('frontend.product_details.right.brand')
                    <!-- shop   -->
                    @include('frontend.product_details.right.shop')
                    <!-- Shipping   -->
                    @include('frontend.product_details.right.shipping')
                    <!-- Refund   -->
                    @if($product->is_refundable)
                    @include('frontend.product_details.right.refund')
                    @endif
                    <!-- icon-section   -->
                    @include('frontend.product_details.right.icon_section')
                    <!-- faq   -->
                    @include('frontend.product_details.right.faq')
                </div>
                 
                </div>
            @endif
        </div>
    </section>

    @include('frontend.smart_bar')

</div>
@endsection

@section('modal')
    <!-- Image Modal -->
    <div class="modal fade" id="image_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="p-4">
                    <div class="size-300px size-lg-450px">
                        <img class="img-fit h-100 lazyload"
                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                            data-src=""
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Modal -->
    <div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title fw-600 h5">{{ translate('Any query about this product') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" action="{{ route('conversations.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="form-group">
                            <input type="text" class="form-control mb-3 rounded-0" name="title"
                                value="{{ $detailedProduct->name }}" placeholder="{{ translate('Product Name') }}"
                                required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control rounded-0" rows="8" name="message" required
                                placeholder="{{ translate('Your Question') }}">{{ route('product', $detailedProduct->slug) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary fw-600 rounded-0"
                            data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary fw-600 rounded-0 w-100px">{{ translate('Send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bid Modal -->
    @if($detailedProduct->auction_product == 1)
        @php 
            $highest_bid = $detailedProduct->bids->max('amount');
            $min_bid_amount = $highest_bid != null ? $highest_bid+1 : $detailedProduct->starting_bid; 
        @endphp
        <div class="modal fade" id="bid_for_detail_product" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ translate('Bid For Product') }} <small>({{ translate('Min Bid Amount: ').$min_bid_amount }})</small> </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="form-horizontal" action="{{ route('auction_product_bids.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                            <div class="form-group">
                                <label class="form-label">
                                    {{translate('Place Bid Price')}}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="form-group">
                                    <input type="number" step="0.01" class="form-control form-control-sm" name="amount" min="{{ $min_bid_amount }}" placeholder="{{ translate('Enter Amount') }}" required>
                                </div>
                            </div>
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-sm btn-primary transition-3d-hover mr-1">{{ translate('Submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Product Review Modal -->
    <div class="modal fade" id="product-review-modal">
        <div class="modal-dialog">
            <div class="modal-content" id="product-review-modal-content">

            </div>
        </div>
    </div>

    <!-- Size chart show Modal -->
    @include('modals.size_chart_show_modal')

    <!-- Product Warranty Modal -->
    <div class="modal fade" id="warranty-note-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ translate('Warranty Note') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body c-scrollbar-light">
                    @if($detailedProduct->warranty_note_id != null)
                        <p>{{ $detailedProduct->warrantyNote->getTranslation('description') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Product Refund Modal -->
    <div class="modal fade" id="refund-note-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ translate('Refund Note') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body c-scrollbar-light">
                    @if($detailedProduct->refund_note_id != null)
                        <p>{{ $detailedProduct->refundNote->getTranslation('description') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            getVariantPrice();
        });

        (function () {
            const statsRoot = document.getElementById('product-view-stats');
            if (!statsRoot) return;
            const todayEl = document.getElementById('product-today-views');
            const liveEl = document.getElementById('product-live-viewers');
            const url = "{{ route('product.view-stats', ['product' => $detailedProduct->id]) }}";
            const refreshMs = 10000;

            const render = (data) => {
                if (todayEl && typeof data.today_views !== 'undefined') {
                    todayEl.textContent = data.today_views;
                }
                if (liveEl && typeof data.live_viewers !== 'undefined') {
                    liveEl.textContent = data.live_viewers;
                }
            };

            const fetchStats = () => {
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((res) => res.ok ? res.json() : Promise.reject())
                    .then(render)
                    .catch(() => {
                        // keep UI silent on failure
                    });
            };

            fetchStats();
            setInterval(fetchStats, refreshMs);
        })();

        function CopyToClipboard(e) {
            var url = $(e).data('url');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(url).select();
            try {
                document.execCommand("copy");
                AIZ.plugins.notify('success', '{{ translate('Link copied to clipboard') }}');
            } catch (err) {
                AIZ.plugins.notify('danger', '{{ translate('Oops, unable to copy') }}');
            }
            $temp.remove();
            // if (document.selection) {
            //     var range = document.body.createTextRange();
            //     range.moveToElementText(document.getElementById(containerid));
            //     range.select().createTextRange();
            //     document.execCommand("Copy");

            // } else if (window.getSelection) {
            //     var range = document.createRange();
            //     document.getElementById(containerid).style.display = "block";
            //     range.selectNode(document.getElementById(containerid));
            //     window.getSelection().addRange(range);
            //     document.execCommand("Copy");
            //     document.getElementById(containerid).style.display = "none";

            // }
            // AIZ.plugins.notify('success', 'Copied');
        }

        function show_chat_modal() {
            @if (Auth::check())
                $('#chat_modal').modal('show');
            @else
                $('#login_modal').modal('show');
            @endif
        }

        // Pagination using ajax
        $(window).on('hashchange', function() {
            if(window.history.pushState) {
                window.history.pushState('', '/', window.location.pathname);
            } else {
                window.location.hash = '';
            }
        });

        $(document).ready(function() {
            $(document).on('click', '.product-queries-pagination .pagination a', function(e) {
                getPaginateData($(this).attr('href').split('page=')[1], 'query', 'queries-area');
                e.preventDefault();
            });
        });

        $(document).ready(function() {
            $(document).on('click', '.product-reviews-pagination .pagination a', function(e) {
                getPaginateData($(this).attr('href').split('page=')[1], 'review', 'reviews-area');
                e.preventDefault();
            });
        });

        function getPaginateData(page, type, section) {
            $.ajax({
                url: '?page=' + page,
                dataType: 'json',
                data: {type: type},
            }).done(function(data) {
                $('.'+section).html(data);
                location.hash = page;
            }).fail(function() {
                alert('Something went worng! Data could not be loaded.');
            });
        }
        // Pagination end

        function showImage(photo) {
            $('#image_modal img').attr('src', photo);
            $('#image_modal img').attr('data-src', photo);
            $('#image_modal').modal('show');
        }

        function bid_modal(){
            @if (isCustomer() || isSeller())
                $('#bid_for_detail_product').modal('show');
          	@elseif (isAdmin())
                AIZ.plugins.notify('warning', '{{ translate("Sorry, Only customers & Sellers can Bid.") }}');
            @else
                $('#login_modal').modal('show');
            @endif
        }

        function product_review(product_id,order_id) {
            @if (isCustomer())
                @if ($review_status == 1)
                    $.post('{{ route('product_review_modal') }}', {
                        _token: '{{ @csrf_token() }}',
                        product_id: product_id,
                        order_id: order_id
                    }, function(data) {
                        $('#product-review-modal-content').html(data);
                        $('#product-review-modal').modal('show', {
                            backdrop: 'static'
                        });
                        AIZ.extra.inputRating();
                    });
                @else
                    AIZ.plugins.notify('warning', '{{ translate("Sorry, You need to buy this product to give review.") }}');
                @endif
            @elseif (Auth::check() && !isCustomer())
                AIZ.plugins.notify('warning', '{{ translate("Sorry, Only customers can give review.") }}');
            @else
                $('#login_modal').modal('show');
            @endif
        }

        function showSizeChartDetail(id, name){
            $('#size-chart-show-modal .modal-title').html('');
            $('#size-chart-show-modal .modal-body').html('');
            if (id == 0) {
                AIZ.plugins.notify('warning', '{{ translate("Sorry, There is no size guide found for this product.") }}');
                return false;
            }
            $.ajax({
                type: "GET",
                url: "{{ route('size-charts-show', '') }}/"+id,
                data: {},
                success: function(data) {
                    $('#size-chart-show-modal .modal-title').html(name);
                    $('#size-chart-show-modal .modal-body').html(data);
                    $('#size-chart-show-modal').modal('show');
                }
            });
        }

        function getRandomNumber(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        function updateViewerCount() {
            const countElement = document.querySelector('#live-product-viewing-visitors .count');
            const min = parseInt(`{{ get_setting('min_custom_product_visitors') }}`);
            const max = parseInt(`{{ get_setting('max_custom_product_visitors') }}`);
            const randomNumber = getRandomNumber(min, max);
            countElement.textContent = randomNumber;
            const randomTime = getRandomNumber(5000, 10000);
            setTimeout(updateViewerCount, randomTime);
        }
        
    </script>
    @if(get_setting('show_custom_product_visitors')==1)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            updateViewerCount();
        });
    </script>
    @endif

@endsection
