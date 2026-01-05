@if(count($todays_deal_products) > 0)
    <section  class="mb-2 mb-md-3 mt-2 mt-md-3">
        <div class="container">
            @php
                $lang = get_system_language()->code;
                $todays_deal_banner = get_setting('todays_deal_banner', null, $lang);
                $todays_deal_banner_small = get_setting('todays_deal_banner_small', null, $lang);
            @endphp
            <div class="row no-gutters">
                <!-- Banner -->
                @if ($todays_deal_banner != null || $todays_deal_banner_small != null)
                    <div class="col-xl-5">
                        <div class="overflow-hidden h-100 d-none d-md-block">
                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" 
                                data-src="{{ uploaded_asset($todays_deal_banner) }}" 
                                alt="{{ env('APP_NAME') }} promo" class="lazyload img-fit h-100 has-transition" 
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                        </div>
                        <div class="overflow-hidden h-100 d-md-none">
                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" 
                                data-src="{{ $todays_deal_banner_small != null ? uploaded_asset($todays_deal_banner_small) : uploaded_asset($todays_deal_banner) }}" 
                                alt="{{ env('APP_NAME') }} promo" class="lazyload img-fit h-100 has-transition" 
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                        </div>
                    </div>
                @endif
                <!-- Products -->
                @php
                    $todays_deal_banner_text_color =  ((get_setting('todays_deal_banner_text_color') == 'light') ||  (get_setting('todays_deal_banner_text_color') == null)) ? 'text-white' : 'text-dark';
                    $col_val = $todays_deal_banner != null ? 'col-xl-7' : 'col-xl-12';
                    $xxl_items = $todays_deal_banner != null ? 5 : 7;
                    $xl_items = $todays_deal_banner != null ? 4 : 6;
                @endphp
                <div class="{{ $col_val }}" style="background-color:#000000;">
                    <div class="d-flex flex-wrap align-items-baseline justify-content-between px-4 px-xl-5 pt-4">
                        <h3 class="fs-16 fs-md-20 fw-700 mb-2 text-white mb-sm-0"><b>Today’s</b> Hot Deals</h3>
                        <a href="{{ route('todays-deal') }}" class="fs-12 fw-700 {{ $todays_deal_banner_text_color }} has-transition hov-text-secondary-base">{{ translate('View All') }}</a>
                    </div>
                    <div class="c-scrollbar-light overflow-hidden px-4 px-md-5 pb-3 pt-3 pt-md-3 pb-md-5">
                        <div class="h-100 d-flex flex-column justify-content-center">
                            <div class="todays-deal aiz-carousel" data-items="{{ $xxl_items }}" data-xxl-items="{{ $xxl_items }}" data-xl-items="{{ $xxl_items }}" data-lg-items="5" data-md-items="4" data-sm-items="3" data-xs-items="2" data-arrows="true" data-dots="false" data-autoplay="true" data-infinite="true">
                                @foreach ($todays_deal_products as $key => $product)
                                    <div class="carousel-box h-100 px-3 px-lg-0">
                                        <a href="{{ route('product', $product->slug) }}" class="h-100 overflow-hidden hov-scale-img mx-auto" title="{{  $product->getTranslation('name')  }}">
                                            <!-- Image -->
                                            <div class="img h-80px w-80px rounded-content overflow-hidden mx-auto">
                                                <img class="lazyload img-fit m-auto has-transition"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ get_image($product->thumbnail) }}"
                                                    alt="{{ $product->getTranslation('name') }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                            </div>
                                            <!-- Price -->
                                            <div class="fs-14 mt-3 text-center">
                                                <span class="d-block {{ $todays_deal_banner_text_color }} fw-700">{{ home_discounted_base_price($product) }}</span>
                                                @if(home_base_price($product) != home_discounted_base_price($product))
                                                    <del class="d-block text-secondary fw-400">{{ home_base_price($product) }}</del>
                                                @else
                                                  <del class="d-block text-secondary fw-400">{{ purchase_price($product) }}</del>
                                                @endif
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endif

@if(count($todays_deal_products) > 0)
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    :root {
      --item1-transform: translateX(-100%) translateY(-5%) scale(1.5);
      --item1-filter: blur(30px);
      --item1-zIndex: 11;
      --item1-opacity: 0;

      --item2-transform: translateX(0);
      --item2-filter: blur(0px);
      --item2-zIndex: 10;
      --item2-opacity: 1;

      --item3-transform: translate(50%,10%) scale(0.8);
      --item3-filter: blur(10px);
      --item3-zIndex: 9;
      --item3-opacity: 1;

      --item4-transform: translate(90%,20%) scale(0.5);
      --item4-filter: blur(30px);
      --item4-zIndex: 8;
      --item4-opacity: 1;

      --item5-transform: translate(120%,30%) scale(0.3);
      --item5-filter: blur(40px);
      --item5-zIndex: 7;
      --item5-opacity: 0;
      
      --primary-color: #c70909;
      --secondary-color: #ad0707;
    }

    /* carousel */
    .carousel-container {
      position: relative;
      margin: 20px 0;
    }

    .carousel {
      position: relative;
      height: 800px;
      overflow: hidden;
      margin-top: -100px;
    }

    .carousel .list {
      position: absolute;
      width: 1140px;
      max-width: 90%;
      height: 100%;
      left: 50%;
      transform: translateX(-50%);
    }

    .carousel .list .item {
      position: absolute;
      left: 0%;
      width: 80%;
      height: 100%;
      font-size: 15px;
      transition: left 0.5s, opacity 0.5s, width 0.5s;
    }

    .carousel .list .item:nth-child(n + 6) {
      opacity: 0;
    }

    .carousel .list .item:nth-child(2) {
      z-index: 10;
      transform: translateX(0);
    }

    .carousel .list .item img {
      width: 50%;
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-50%);
      transition: right 1.5s;
      object-fit: contain;
      max-height: 80%;
    }

    .carousel .list .item .introduce {
      opacity: 0;
      padding: 10px;
      pointer-events: none;
    }

    .carousel .list .item:nth-child(2) .introduce {
      opacity: 1;
      pointer-events: auto;
      width: 400px;
      padding: 10px;
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      transition: opacity 0.5s;
    }

    .carousel .list .item .introduce .title {
      font-size: 2em;
      font-weight: 500;
      line-height: 1em;
      margin-bottom: 10px;
    }

    .carousel .list .item .introduce .topic {
      font-size: 2em;
      color: #c70909;
      font-weight: 500;
      margin-bottom: 15px;
    }

    .carousel .list .item .introduce .des {
      font-size: small;
      color: #5559;
      margin-bottom: 20px;
      line-height: 1.5;
    }

    .carousel .list .item .introduce .seeMore {
      font-family: Poppins, sans-serif;
      margin-top: 1.2em;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      background-color: var(--primary-color);
      color: white;
      font-weight: bold;
      letter-spacing: 1px;
      transition: background 0.3s;
      cursor: pointer;
    }

    .carousel .list .item .introduce .seeMore:hover {
      background: #c70909;
    }

    .carousel .list .item:nth-child(1) {
      transform: var(--item1-transform);
      filter: var(--item1-filter);
      z-index: var(--item1-zIndex);
      opacity: var(--item1-opacity);
      pointer-events: none;
    }

    .carousel .list .item:nth-child(3) {
      transform: var(--item3-transform);
      filter: var(--item3-filter);
      z-index: var(--item3-zIndex);
    }

    .carousel .list .item:nth-child(4) {
      transform: var(--item4-transform);
      filter: var(--item4-filter);
      z-index: var(--item4-zIndex);
    }

    .carousel .list .item:nth-child(5) {
      transform: var(--item5-transform);
      filter: var(--item5-filter);
      opacity: var(--item5-opacity);
      pointer-events: none;
    }

    /* animation text in item2 */
    .carousel .list .item:nth-child(2) .introduce .title,
    .carousel .list .item:nth-child(2) .introduce .topic,
    .carousel .list .item:nth-child(2) .introduce .des,
    .carousel .list .item:nth-child(2) .introduce .seeMore {
      opacity: 0;
      animation: showContent 0.5s 1s ease-in-out 1 forwards;
    }

    @keyframes showContent {
      from {
        transform: translateY(-30px);
        filter: blur(10px);
      }
      to {
        transform: translateY(0);
        opacity: 1;
        filter: blur(0px);
      }
    }

    .carousel .list .item:nth-child(2) .introduce .topic {
      animation-delay: 1.2s;
    }

    .carousel .list .item:nth-child(2) .introduce .des {
      animation-delay: 1.4s;
    }

    .carousel .list .item:nth-child(2) .introduce .seeMore {
      animation-delay: 1.6s;
    }

    /* next click animations */
    .carousel.next .item:nth-child(1) {
      animation: transformFromPosition2 0.5s ease-in-out 1 forwards;
    }

    @keyframes transformFromPosition2 {
      from {
        transform: var(--item2-transform);
        filter: var(--item2-filter);
        opacity: var(--item2-opacity);
      }
    }

    .carousel.next .item:nth-child(2) {
      animation: transformFromPosition3 0.7s ease-in-out 1 forwards;
    }

    @keyframes transformFromPosition3 {
      from {
        transform: var(--item3-transform);
        filter: var(--item3-filter);
        opacity: var(--item3-opacity);
      }
    }

    .carousel.next .item:nth-child(3) {
      animation: transformFromPosition4 0.9s ease-in-out 1 forwards;
    }

    @keyframes transformFromPosition4 {
      from {
        transform: var(--item4-transform);
        filter: var(--item4-filter);
        opacity: var(--item4-opacity);
      }
    }

    .carousel.next .item:nth-child(4) {
      animation: transformFromPosition5 1.1s ease-in-out 1 forwards;
    }

    @keyframes transformFromPosition5 {
      from {
        transform: var(--item5-transform);
        filter: var(--item5-filter);
        opacity: var(--item5-opacity);
      }
    }

    /* prev click animations */
    .carousel.prev .list .item:nth-child(5) {
      animation: transformFromPosition4 0.5s ease-in-out 1 forwards;
    }

    .carousel.prev .list .item:nth-child(4) {
      animation: transformFromPosition3 0.7s ease-in-out 1 forwards;
    }

    .carousel.prev .list .item:nth-child(3) {
      animation: transformFromPosition2 0.9s ease-in-out 1 forwards;
    }

    .carousel.prev .list .item:nth-child(2) {
      animation: transformFromPosition1 1.1s ease-in-out 1 forwards;
    }

    @keyframes transformFromPosition1 {
      from {
        transform: var(--item1-transform);
        filter: var(--item1-filter);
        opacity: var(--item1-opacity);
      }
    }

    /* detail  */
    .carousel .list .item .detail {
      opacity: 0;
      padding:10px;
      pointer-events: none;
    }

    .carousel.showDetail .list .item:nth-child(3),
    .carousel.showDetail .list .item:nth-child(4) {
      left: 100%;
      opacity: 0;
      pointer-events: none;
    }

    .carousel.showDetail .list .item:nth-child(2) {
      width: 100%;
    }

    .carousel.showDetail .list .item:nth-child(2) .introduce {
      opacity: 0;
      pointer-events: none;
    }

    .carousel.showDetail .list .item:nth-child(2) img {
      right: 50%;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail {
      opacity: 1;
      width: 50%;
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-50%);
      text-align: right;
      pointer-events: auto;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .title {
      font-size: 2.5em;
      margin-bottom: 15px;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .price {
      font-size: 2em;
      color: var(--primary-color);
      margin-bottom: 15px;
      font-weight: 600;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .des {
      font-size: 1em;
      color: #555;
      margin-bottom: 20px;
      line-height: 1.6;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .specifications {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      width: 100%;
      border-top: 1px solid #5553;
      padding-top: 20px;
      margin-bottom: 25px;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .specifications div {
      min-width: 90px;
      text-align: center;
      flex-shrink: 0;
      background: #f5f5f5;
      padding: 10px;
      border-radius: 8px;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .specifications div p:nth-child(1) {
      font-weight: bold;
      margin-bottom: 5px;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .specifications div p:nth-child(2) {
      color: #666;
    }

    .carousel.showDetail .list .item:nth-child(2) .checkout button {
      font-family: Poppins, sans-serif;
      padding: 12px 25px;
      border-radius: 4px;
      margin-left: 10px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s;
      border: 1px solid #ddd;
    }

    .carousel.showDetail .list .item:nth-child(2) .checkout button.add-to-cart {
      background-color: #fff;
      color: #333;
    }

    .carousel.showDetail .list .item:nth-child(2) .checkout button.add-to-cart:hover {
      background-color: #f5f5f5;
    }

    .carousel.showDetail .list .item:nth-child(2) .checkout button.checkout {
      background-color: var(--primary-color);
      color: #fff;
      border: 1px solid var(--primary-color);
    }

    .carousel.showDetail .list .item:nth-child(2) .checkout button.checkout:hover {
      background-color: #c70909;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .title,
    .carousel.showDetail .list .item:nth-child(2) .detail .price,
    .carousel.showDetail .list .item:nth-child(2) .detail .des,
    .carousel.showDetail .list .item:nth-child(2) .detail .specifications,
    .carousel.showDetail .list .item:nth-child(2) .detail .checkout {
      opacity: 0;
      animation: showContent 0.5s 1s ease-in-out 1 forwards;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .price {
      animation-delay: 1.1s;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .des {
      animation-delay: 1.2s;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .specifications {
      animation-delay: 1.3s;
    }

    .carousel.showDetail .list .item:nth-child(2) .detail .checkout {
      animation-delay: 1.4s;
    }

    .arrows {
      position: absolute;
      bottom: 10px;
      width: 1140px;
      max-width: 90%;
      display: flex;
      justify-content: space-between;
      left: 50%;
      transform: translateX(-50%);
      z-index: 20;
    }

    #prev,
    #next {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      font-family: monospace;
      border: 1px solid #5555;
      font-size: 20px;
      cursor: pointer;
      transition: all 0.3s ease;
      background: #fff;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #prev:hover,
    #next:hover {
      background: var(--primary-color);
      color: #fff;
      border-color: var(--primary-color);
    }

    #prev:disabled,
    #next:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    #back {
      position: absolute;
      z-index: 100;
      bottom: 0%;
      left: 50%;
      transform: translateX(-50%);
      border: none;
      border-radius: 4px;
      font-family: Poppins, sans-serif;
      font-weight: bold;
      letter-spacing: 1px;
      background-color: var(--primary-color);
      color: white;
      padding: 10px 20px;
      transition: all 0.5s;
      cursor: pointer;
    }

    #back:hover {
      background: #c70909;
    }

    .carousel.showDetail #back {
      opacity: 1;
    }

    .carousel.showDetail #prev,
    .carousel.showDetail #next {
      opacity: 0;
      pointer-events: none;
    }

    .carousel::before {
      width: 500px;
      height: 300px;
      content: '';
      background-image: linear-gradient(70deg, var(--secondary-color), var(--primary-color));
      position: absolute;
      z-index: -1;
      border-radius: 20% 30% 80% 10%;
      filter: blur(150px);
      top: 50%;
      left: 50%;
      transform: translate(-10%, -50%);
      transition: 1s;
    }

    .carousel.showDetail::before {
      transform: translate(-100%, -50%) rotate(90deg);
      filter: blur(130px);
    }

    /* autoplay progress bar */
    .carousel::after {
      content: '';
      position: absolute;
      bottom: 5px;
      left: 50%;
      transform: translateX(-50%);
      width: 120px;
      height: 4px;
      background: rgba(0, 0, 0, 0.1);
      border-radius: 2px;
      overflow: hidden;
    }

    .carousel.playing::after {
      background: linear-gradient(to right, var(--primary-color) 100%, transparent 0);
      animation: progress 5s linear infinite;
    }

    @keyframes progress {
      from { background-position: 0 0; }
      to { background-position: -120px 0; }
    }

    /* Product badges */
    .product-badge {
      position: absolute;
      top: 15px;
      left: 15px;
      background: var(--secondary-color);
      color: white;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: bold;
      z-index: 5;
    }

    /* Loading state */
    .carousel-loading {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 400px;
    }

    /* No products message */
    .no-products {
      text-align: center;
      padding: 40px;
      font-size: 18px;
      color: #666;
    }

    /* Accessibility improvements */
    .carousel .list .item:focus {
      outline: 2px solid var(--primary-color);
      outline-offset: 2px;
    }

    /* Keyboard navigation hints */
    .carousel-hints {
      text-align: center;
      margin-top: 10px;
      font-size: 12px;
      color: #666;
    }

    @media screen and (max-width: 991px) {
      /* ipad, tablets */
      .carousel .list .item {
        width: 90%;
      }

      .carousel.showDetail .list .item:nth-child(2) .detail .specifications {
        overflow: auto;
      }

      .carousel.showDetail .list .item:nth-child(2) .detail .title {
        font-size: 2em;
      }
    }

    @media screen and (max-width: 767px) {
      /* mobile */
      .carousel {
        height: 600px;
      }

      .carousel .list .item {
        width: 100%;
        font-size: 10px;
      }

      .carousel .list {
        height: 100%;
      }

      .carousel .list .item:nth-child(2) .introduce {
        width: 50%;
      }

      .carousel .list .item img {
        width: 40%;
      }

      .carousel.showDetail .list .item:nth-child(2) .detail {
        backdrop-filter: blur(10px);
        font-size: small;
        background: rgba(255, 255, 255, 0.9);
        padding: 15px;
        border-radius: 10px;
        width: 45%;
      }

      .carousel .list .item:nth-child(2) .introduce .des,
      .carousel.showDetail .list .item:nth-child(2) .detail .des {
        height: 100px;
        overflow: auto;
      }

      .carousel.showDetail .list .item:nth-child(2) .detail .checkout {
        display: flex;
        width: max-content;
        float: right;
        flex-direction: column;
        gap: 10px;
      }

      .carousel.showDetail .list .item:nth-child(2) .detail .checkout button {
        margin-left: 0;
      }

      .carousel.showDetail .list .item:nth-child(2) .detail .specifications {
        flex-direction: column;
        gap: 10px;
      }

      .arrows {
        flex-direction: column;
        align-items: center;
        gap: 10px;
      }

      #back {
        position: relative;
        left: auto;
        transform: none;
        margin-top: 10px;
      }
    }
  </style>
    
 <div class="carousel">
        <div class="list">
            @foreach ($todays_deal_products as $key => $product)
             <div class="item">
                <!-- <div class="product-badge">Today's Deal</div> -->
                
                <img class="lazyload img-fit m-auto has-transition"
                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                    data-src="{{ get_image($product->thumbnail) }}"
                    alt="{{ $product->getTranslation('name') }}"
                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                <div class="introduce">
                    <div class="title">
                        {{ \Illuminate\Support\Str::words(strip_tags($product->getTranslation('name')), 10, '...') }}
                    </div>
                    <div class="topic">{{ $product->brand->name }}</div>
                    <div class="des">
                     {{ \Illuminate\Support\Str::words(strip_tags($product->getTranslation('description')), 20, '...') }}
                    </div>
                    <button class="seeMore" aria-label="See more details about {{ $product->getTranslation('name') }}">SEE MORE &#8599</button>
                </div>
                <div class="detail">
                    <div class="title">
                        {{ \Illuminate\Support\Str::words($product->getTranslation('name'), 10, '...') }}
                    </div>
                    <div class="price">
                        @if($product->unit_price)
                            {{ single_price($product->unit_price) }}
                            @if($product->discount > 0)
                                <span style="text-decoration: line-through; color: #999; font-size: 0.7em; margin-left: 5px;">
                                    {{ single_price($product->unit_price + $product->discount) }}
                                </span>
                            @endif
                        @else
                            {{ single_price($product->min_price) }} - {{ single_price($product->max_price) }}
                        @endif
                    </div>
                    <div class="des">
                        {{ \Illuminate\Support\Str::words(strip_tags($product->getTranslation('description')), 50, '...') }}
                    </div>
                    <div class="specifications">
                        @if($product->brand)
                        <div>
                            <p>Brand</p>
                            <p>{{ $product->brand->name }}</p>
                        </div>
                        @endif
                        @if($product->conditon)
                        <div>
                            <p>Condition</p>
                            <p>{{ $product->condition }}</p>
                        </div>
                        @endif
                        @if($product->weight)
                        <div>
                            <p>Weight</p>
                            <p>{{ $product->weight }} kg</p>
                        </div>
                        @endif
                         @if($product->current_stock > 0)
                        <div>
                            <p>In Stock</p>
                            <p>{{ $product->current_stock }}</p>
                        </div>
                         @endif
                        @if($product->discount > 0)
                        <div>
                            <p>Discount</p>
                            <p>{{ $product->discount }}%</p>
                        </div>
                        @endif
                    </div>
                    <div class="checkout">
                       
                    <a href="javascript:void(0)" class="hov-svg-white" onclick="addToWishList({{ $product->id }})"
                        data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.4" viewBox="0 0 16 14.4">
                            <g id="_51a3dbe0e593ba390ac13cba118295e4" data-name="51a3dbe0e593ba390ac13cba118295e4"
                                transform="translate(-3.05 -4.178)">
                                <path id="Path_32649" data-name="Path 32649"
                                    d="M11.3,5.507l-.247.246L10.8,5.506A4.538,4.538,0,1,0,4.38,11.919l.247.247,6.422,6.412,6.422-6.412.247-.247A4.538,4.538,0,1,0,11.3,5.507Z"
                                    transform="translate(0 0)" fill="#919199" />
                                <path id="Path_32650" data-name="Path 32650"
                                    d="M11.3,5.507l-.247.246L10.8,5.506A4.538,4.538,0,1,0,4.38,11.919l.247.247,6.422,6.412,6.422-6.412.247-.247A4.538,4.538,0,1,0,11.3,5.507Z"
                                    transform="translate(0 0)" fill="#919199" />
                            </g>
                        </svg>
                    </a>
                    
                    <a href="{{ route('product', $product->slug) }}" class="hov-svg-white" title="{{  $product->getTranslation('name')  }}">
                           <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#919199">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                            </svg>
                    </a>
                 <!-- add to cart -->
                             <button type="button" class="btn btn-secondary-base mr-2 add-to-cart fw-600 min-w-150px rounded-0"
                                href="javascript:void(0)"
                                onclick="showAddToCartModal({{ $product->id }})">
                                <i class="las la-shopping-bag"></i> {{ translate('Add to cart') }}
                            </button>
                            
                          <!--  <button type="button" class="btn btn-secondary-base mr-2 add-to-cart fw-600 min-w-150px rounded-0"
                                href="javascript:void(0)"
                                onclick="addToWishList({{ $product->id }})">
                                <i class="las la-heart"></i> {{ translate('Add to wishlist') }}
                            </button> -->
                            
                          
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
     <div class="arrows">
            <button id="prev"><</button>
            <button id="next">></button>
            <button id="back">See All  &#8599</button>
        </div>
</div>



   <script>
    let nextButton = document.getElementById('next');
let prevButton = document.getElementById('prev');
let carousel = document.querySelector('.carousel');
let listHTML = document.querySelector('.carousel .list');
let seeMoreButtons = document.querySelectorAll('.seeMore');
let backButton = document.getElementById('back');

nextButton.onclick = function(){
    showSlider('next');
}
prevButton.onclick = function(){
    showSlider('prev');
}
let unAcceppClick;
const showSlider = (type) => {
    nextButton.style.pointerEvents = 'none';
    prevButton.style.pointerEvents = 'none';

    carousel.classList.remove('next', 'prev');
    let items = document.querySelectorAll('.carousel .list .item');
    if(type === 'next'){
        listHTML.appendChild(items[0]);
        carousel.classList.add('next');
    }else{
        listHTML.prepend(items[items.length - 1]);
        carousel.classList.add('prev');
    }
    clearTimeout(unAcceppClick);
    unAcceppClick = setTimeout(()=>{
        nextButton.style.pointerEvents = 'auto';
        prevButton.style.pointerEvents = 'auto';
    }, 2000)
}
seeMoreButtons.forEach((button) => {
    button.onclick = function(){
        carousel.classList.remove('next', 'prev');
        carousel.classList.add('showDetail');
    }
});
backButton.onclick = function(){
    carousel.classList.remove('showDetail');
}
</script>
@endif