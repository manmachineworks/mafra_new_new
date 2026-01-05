@extends('frontend.layouts.app')

@section('content')
<style>
    @media (max-width: 767px) {
        #flash_deal .flash-deals-baner {
            height: 203px !important;
        }
    }
        #todays_deal {
        min-height: 200px;  /* adjust to average card height */
        }
    </style>

    
@php $lang = get_system_language()->code; @endphp
  <!-- Sliders -->
    <!-- Sliders -->
    <div class="home-banner-area mb-3" style="background-color:#000000;">
        <div class="@if(get_setting('slider_section_full_width') == 1) container-fluid px-0 @else container @endif">
            <!-- Sliders -->
            <div class="home-slider slider-full">
                @if (get_setting('home_slider_images', null, $lang) != null)
                    <div class="aiz-carousel dots-inside-bottom mobile-img-auto-height" data-autoplay="true" data-infinite="true">
                        @php
                            $decoded_slider_images = json_decode(get_setting('home_slider_images', null, $lang), true);
                            $sliders = get_slider_images($decoded_slider_images);
                            $home_slider_links = get_setting('home_slider_links', null, $lang);
                        @endphp
                        @foreach ($sliders as $key => $slider)
                            <div class="carousel-box">
                                <a href="{{ isset(json_decode($home_slider_links, true)[$key]) ? json_decode($home_slider_links, true)[$key] : '' }}">
                                    <!-- Image -->
                                    <div class="d-block mw-100 img-fit overflow-hidden h-180px h-sm-200px h-md-250px h-lg-300px h-xl-370px overflow-hidden">
                                        <img class="img-fit h-100 m-auto has-transition ls-is-cached lazyloaded"
                                        src="{{ $slider ? my_asset($slider->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                        alt="{{ env('APP_NAME') }} promo"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    
<style>
/* Category Box */
.slidcat {
    margin-top: 20px;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 8px 20px rgba(31, 38, 135, 0.25);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    transition: transform 0.4s ease, box-shadow 0.4s ease;
}
.slidcat:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

/* Text */
.slidtaxt {
    max-width: 90px;
    font-size: 13px;
    line-height: 1.3;
}

/* Responsive tweaks */
@media (max-width: 767.98px) {
    .slidcan {
        margin: 0;
        padding: 0;
        width: 100%;
    }
    .carousel-box {
        padding: 0 8px;
    }
    .slidcat {
        margin-top: 15px;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(31, 38, 135, 0.25);
    }
    .slidtaxt {
        font-size: 12px;
        max-width: 70px;
    }
}

@media (max-width: 480px) {
    .slidtaxt {
        font-size: 11px;
        max-width: 65px;
    }
    .slidcat {
        margin-top: 10px;
    }
}
</style>

    
    @if (count($featured_categories) > 0)
<section class="mb-3 mb-md-4 pt-2 pt-md-4">
    <div class="container slidcan">
        <div class="bg-white px-sm-3">
            <div class="aiz-carousel sm-gutters-17" 
                 data-items="8" data-xxl-items="8" data-xl-items="7"
                 data-lg-items="6" data-md-items="5" data-sm-items="3" data-xs-items="2"
                 data-arrows="false" data-dots="false" data-autoplay="true"
                 data-infinite="true" data-center="false">
                 
                @foreach ($featured_categories as $key => $category)
                    @php
                        $category_name = $category->getTranslation('name');
                    @endphp
                    <div class="carousel-box px-3 d-flex flex-column align-items-center">
                        <div class="slidcat size-80px overflow-hidden hov-scale-img">
                            <a href="{{ route('products.category', $category->slug) }}">
                                <img src="{{ isset($category->bannerImage->file_name) ? my_asset($category->bannerImage->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                     class="lazyload img-fit h-100 mx-auto has-transition"
                                     alt="{{ $category_name }}"
                                     onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                            </a>
                        </div>
                        <div class="text-center slidtaxt mt-2">
                            <a class="fs-13 fw-500 text-reset hov-text-primary d-inline-block text-truncate"
                               href="{{ route('products.category', $category->slug) }}">
                                {{ $category_name }}
                            </a>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</section>
@endif

<input type="hidden" id="selected_homepage" value="{{get_setting('homepage_select')}}">



  @php $homeBanner6Images = get_setting('home_banner6_images', null, $lang);   @endphp
    @if ($homeBanner6Images != null)
        <div class="mb-2 mb-md-3 mt-2 mt-md-3">
            <div class="container">
                @php
                    $banner_6_imags = json_decode($homeBanner6Images);
                    $data_md = count($banner_6_imags) >= 2 ? 2 : 1;
                    $home_banner6_links = get_setting('home_banner6_links', null, $lang);
                @endphp
                <div class="aiz-carousel gutters-16 overflow-hidden arrow-inactive-none arrow-dark arrow-x-15"
                    data-items="{{ count($banner_6_imags) }}" data-xxl-items="{{ count($banner_6_imags) }}"
                    data-xl-items="{{ count($banner_6_imags) }}" data-lg-items="{{ $data_md }}"
                    data-md-items="{{ $data_md }}" data-sm-items="1" data-xs-items="1" data-arrows="true"
                    data-dots="false">
                    @foreach ($banner_6_imags as $key => $value)
                        <div class="carousel-box overflow-hidden hov-scale-img">
                            <a href="{{ isset(json_decode($home_banner6_links, true)[$key]) ? json_decode($home_banner6_links, true)[$key] : '' }}"
                                class="d-block text-reset overflow-hidden">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                                    class="img-fluid lazyload w-100 has-transition"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

<!-- Best Selling And Todays Deal -->
<section class="pt-4 pt-lg-5 pb-4 ">
    <div class="container">    
        <div class="d-sm-flex" style="box-shadow:
    0 2px 8px rgba(0, 0, 0, 0.08),
    0 8px 20px rgba(0, 0, 0, 0.12); padding: 15px; border-radius: 12px;">
            <!-- Best Selling -->
            @php
             $best_selling_products = get_best_selling_products(20);
            @endphp
            @if (count($best_selling_products) > 0)
            <div class="px-0 px-sm-4 w-100 overflow-hidden rounded-75 best-salling-section pt-32px pb-26px" style="background-color: {{ get_setting('best_selling_section_bg_color', '#E7EFEC') }}">
                <!-- Top Section -->
                <div class="d-flex mb-2 mb-md-3 align-items-baseline justify-content-between px-2">
                    <!-- Title -->
                    <h3 class="fs-16 fw-600 mb-2 mb-sm-0">
                        <span class="">{{ translate('Best Selling') }}</span>
                    </h3>
                    <a type="button" class="arrow-next text-white bg-dark view-more-slide-btn d-flex align-items-center" href="{{route('best-selling')}}">
                        <span><i class="las la-angle-right fs-20 fw-600"></i></span>
                        <span class="fs-12 mr-2 text">View All</span>
                    </a>
                </div>
                <div class="aiz-carousel arrow-x-0 arrow-inactive-none" data-items="5"
                    data-xxl-items="5" data-xl-items="5" data-lg-items="5" data-md-items="3" data-sm-items="1"
                    data-xs-items="2" data-arrows="false" data-dots="false" data-autoplay="true" data-infinite="true">
                    @foreach ($best_selling_products as $key => $product)
                        <div class="px-3">
                            <div class="img h-80px w-80px h-lg-100px w-lg-100px  h-xl-130px w-xl-130px h-xxl-170px w-xxl-170px rounded overflow-hidden mx-auto position-relative image-hover-effect">
                                <a href="{{ route('product', $product->slug) }}" title="{{ $product->getTranslation('name') }}">
                                    <img class="lazyload img-fit m-auto has-transition product-main-image"
                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                    data-src="{{ get_image($product->thumbnail) }}"
                                    alt="{{ $product->getTranslation('name') }}"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">

                                    <img
                                    class="lazyload img-fit m-auto has-transition product-main-image product-hover-image position-absolute"
                                    src="{{ get_first_product_image($product->thumbnail, $product->photos) }}"
                                    alt="{{ $product->getTranslation('name') }}"
                                    title="{{ $product->getTranslation('name') }}"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                </a>
                            </div>

                            <!-- Name -->
                            <div class="fs-13 mr-1 mt-3 text-center mt-2 px-4" title="{{ $product->getTranslation('name') }}">
                                <a class="fw-300 text-truncate-2 hov-text-primary text-reset mt-1" href="{{ route('product', $product->slug) }}">{{ $product->getTranslation('name') }}</a>
                            </div>

                            <!-- Price -->
                            <div class="fs-14 mr-1 mt-1 text-center">
                                <span class="d-block fw-700">{{ home_discounted_base_price($product) }} </span>
                                @if (home_base_price($product) != home_discounted_base_price($product))
                                    <del class="d-block text-secondary fs-12 fw-400">{{ home_base_price($product) }} </del>
                                    @else
                                      <del  class="d-block text-secondary fs-12 fw-400">{{ purchase_price($product) }} </del>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- Todays Deal -->
            @endif
            @php
             $todays_deal_products = get_todays_deal_products(20);
            @endphp
            @if (count($todays_deal_products) > 0)
            <div class="px-0 mt-sm-0 ml-sm-4 w-100  w-md-50 w-lg-35 overflow-hidden border border-2 border-dark rounded-75 todays-deal pt-32px pb-26px" style="background-color: {{ get_setting('todays_deal_bg_color', '#ffffff') }}">
                <div class="d-flex mx-3 mb-3 align-items-baseline justify-content-between">
                    <!-- Title -->
                    <h3 class="fs-16 fw-600 mb-2 mb-sm-0">
                        <span class="">{{ translate('Todays Deal') }}</span>
                    </h3>
                    <!-- Links -->
                    <a type="button" class="arrow-next text-white bg-dark view-more-slide-btn d-flex align-items-center" href="{{ route('todays-deal') }}">
                        <span><i class="las la-angle-right fs-20 fw-600"></i></span>
                        <span class="fs-12 mr-2 text">View All</span>
                    </a>
                </div>  
        
                <div class="aiz-carousel arrow-x-0 arrow-inactive-none" data-items="1"
                    data-xxl-items="1" data-xl-items="1" data-lg-items="1" data-md-items="1" data-sm-items="1"
                    data-xs-items="1" data-arrows="true" data-dots="false" data-autoplay="true" data-infinite="true">
                    @foreach ($todays_deal_products as $key => $product)
                        <div class="px-3">
                            <div class="img h-80px w-80px h-lg-100px w-lg-100px  h-xl-130px w-xl-130px h-xxl-170px w-xxl-170px rounded overflow-hidden mx-auto position-relative image-hover-effect">
                                <a href="{{ route('product', $product->slug) }}" title="{{ $product->getTranslation('name') }}">
                                    <img class="lazyload img-fit m-auto has-transition product-main-image"
                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                    data-src="{{ get_image($product->thumbnail) }}"
                                    alt="{{ $product->getTranslation('name') }}"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">

                                    <img
                                    class="lazyload img-fit m-auto has-transition product-main-image product-hover-image position-absolute"
                                    src="{{ get_first_product_image($product->thumbnail, $product->photos) }}"
                                    alt="{{ $product->getTranslation('name') }}"
                                    title="{{ $product->getTranslation('name') }}"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                </a>
                            </div>

                            <!-- Name -->
                            <div class="fs-13 mr-1 mt-3 text-center px-4" title="{{ $product->getTranslation('name') }}">
                                <a class="fw-300 text-truncate-2 hov-text-primary text-reset h-35px" href="{{ route('product', $product->slug) }}">{{ $product->getTranslation('name') }}</a>
                            </div>

                            <!-- Price -->
                            <div class="fs-14 mr-1 mt-1 text-center">
                                <span class="d-block fw-700">{{ home_discounted_base_price($product) }}</span>
                                @if (home_base_price($product) != home_discounted_base_price($product))
                                    <del
                                        class="d-block text-secondary fs-12 fw-400">{{ home_base_price($product) }}</del>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

<!-- Banner section 1 -->
@php $homeBanner1Images = get_setting('home_banner1_images', null, $lang); @endphp
@if ($homeBanner1Images != null)
<div class="pt-3 pt-lg-4 pb-3 mb-1">
    <div class="container">
        @php
        $banner_1_imags = json_decode($homeBanner1Images);
        $data_md = count($banner_1_imags) >= 2 ? 2 : 1;
        $home_banner1_links = get_setting('home_banner1_links', null, $lang);
        @endphp
        <div class="w-100 pr-3 pr-md-0">
            <div class="aiz-carousel gutters-16 overflow-hidden arrow-inactive-none arrow-dark arrow-x-15 home-banner-1"
                data-items="{{ count($banner_1_imags) }}" data-xxl-items="{{ count($banner_1_imags) }}"
                data-xl-items="{{ count($banner_1_imags) }}" data-lg-items="{{ $data_md }}"
                data-md-items="2.5" data-sm-items="2.5" data-xs-items="2" data-arrows="false"
                data-dots="false" data-autoplay="true" data-infinite="true">
                @foreach ($banner_1_imags as $key => $value)
                <div class="carousel-box overflow-hidden hov-scale-img" >
                    <a href="{{ isset(json_decode($home_banner1_links, true)[$key]) ? json_decode($home_banner1_links, true)[$key] : '' }}"
                        class="d-block text-reset overflow-hidden rounded-75 h-100">
                        <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                            data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                            class="lazyload img-fit h-100 "
                            style="border: 1px solid #e2e8f0; border-radius: 12px;background-color: #ffffff;"
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<style>

     .tab-wrap {
  position: relative;
  display: flex;
  flex-wrap: wrap;
  list-style: none;

  max-width: 100%;
  margin: 40px 0;
  padding: 12px 16px; /* slight breathing space inside */
  border-radius: 12px;

  background-color: #fff;

  /* Modern, smooth shadow */
  box-shadow:
    0 2px 8px rgba(0, 0, 0, 0.08),
    0 8px 20px rgba(0, 0, 0, 0.12);

  transition: box-shadow 0.3s ease, transform 0.2s ease;
}

.tab { display: none; }

.tab__content {
  padding: 10px 25px;
  background-color: transparent;
  position: absolute;
  width: 100%;
  z-index: -1;
  opacity: 0;
  left: 0;
  -webkit-transform: translateY(-3px);
  transform: translateY(-3px);
  border-radius: 6px;
}
  
  .tab:checked:nth-of-type(1) ~ .tab__content:nth-of-type(1) {
  opacity: 1;
  -webkit-transition: 0.5s opacity ease-in, 0.2s transform ease;
  transition: 0.5s opacity ease-in, 0.2s transform ease;
  position: relative;
  top: 0;
  z-index: 100;
  -webkit-transform: translateY(0px);
  transform: translateY(0px);
  text-shadow: 0 0 0;
}

.tab:checked:nth-of-type(2) ~ .tab__content:nth-of-type(2) {
  opacity: 1;
  -webkit-transition: 0.5s opacity ease-in, 0.2s transform ease;
  transition: 0.5s opacity ease-in, 0.2s transform ease;
  position: relative;
  top: 0;
  z-index: 100;
  -webkit-transform: translateY(0px);
  transform: translateY(0px);
  text-shadow: 0 0 0;
}

.tab:checked:nth-of-type(3) ~ .tab__content:nth-of-type(3) {
  opacity: 1;
  -webkit-transition: 0.5s opacity ease-in, 0.2s transform ease;
  transition: 0.5s opacity ease-in, 0.2s transform ease;
  position: relative;
  top: 0;
  z-index: 100;
  -webkit-transform: translateY(0px);
  transform: translateY(0px);
  text-shadow: 0 0 0;
}

.tab:checked:nth-of-type(4) ~ .tab__content:nth-of-type(4) {
  opacity: 1;
  -webkit-transition: 0.5s opacity ease-in, 0.2s transform ease;
  transition: 0.5s opacity ease-in, 0.2s transform ease;
  position: relative;
  top: 0;
  z-index: 100;
  -webkit-transform: translateY(0px);
  transform: translateY(0px);
  text-shadow: 0 0 0;
}

.tab:first-of-type:not(:last-of-type) + label {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.tab:not(:first-of-type):not(:last-of-type) + label { border-radius: 0; }

.tab:last-of-type:not(:first-of-type) + label {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

.tab:checked + label {
  background-color: #fff;
  box-shadow: 0 -1px 0 #fff inset;
  cursor: default;
}

.tab:checked + label:hover {
  box-shadow: 0 -1px 0 #fff inset;
  background-color: #fff;
}

.tab + label {
  width: 100%;
  box-shadow: 0 -1px 0 #eee inset;
  border-radius: 6px 6px 0 0;
  cursor: pointer;
  display: block;
  text-decoration: none;
  color: #333;
  -webkit-box-flex: 3;
  -webkit-flex-grow: 3;
  -ms-flex-positive: 3;
  flex-grow: 3;
  text-align: center;
  background-color: #f2f2f2;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  text-align: center;
  -webkit-transition: 0.3s background-color ease, 0.3s box-shadow ease;
  transition: 0.3s background-color ease, 0.3s box-shadow ease;
  height: 50px;
  box-sizing: border-box;
  padding: 15px;
}
@media (min-width:768px) {

.tab + label { width: auto; }
}

.tab + label:hover {
  background-color: #f9f9f9;
  box-shadow: 0 1px 0 #f4f4f4 inset;
}
    </style>

    <div class="mb-4">
    <div class="container">
    <div class="tab-wrap">
    <input type="radio" id="tab1" name="tabGroup1" class="tab" checked>
    <label for="tab1">Interior</label>
    <input type="radio" id="tab2" name="tabGroup1" class="tab">
    <label for="tab2">Exterior</label>
    <input type="radio" id="tab3" name="tabGroup1" class="tab">
    <label for="tab3">Paint Care</label>
    <input type="radio" id="tab4" name="tabGroup1" class="tab">
    <label for="tab4">Detailing Tools</label>
  
    <div class="tab__content">
   
    <img src="{{ static_asset('uploads/all/tIYt4WZP71XzWVoiFGKAReBgi1pfcAFm4KVDt9v3.webp') }}" class="img-fluid lazyload w-100">

    <div class="bg-white">
    <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                        @foreach (get_cached_products(2) as $key => $product)
                            <div class="carousel-box">
                                @include('frontend.'.get_setting('homepage_select').'.partials.product_box_1',['product' => $product])
                            </div>
                        @endforeach
                    </div>
    </div>
</div>
  
  <div class="tab__content">
  <img src="{{ static_asset('uploads/all/tPb2HjWNlafrdf7BngcJadqJWSUdmQmEB3YBiFhL.webp') }}" class="img-fluid lazyload w-100">
  <div class="bg-white">
  <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                        @foreach (get_cached_products(3) as $key => $product)
                            <div class="carousel-box">
                                @include('frontend.'.get_setting('homepage_select').'.partials.product_box_1',['product' => $product])
                            </div>
                        @endforeach
                    </div>
    </div>
  </div>
  
  <div class="tab__content">
  <img src="{{ static_asset('uploads/all/dubKxAe41305215haXR3x8a1G5Gn8lZRuqNRO13C.webp') }}" class="img-fluid lazyload w-100">
  <div class="bg-white">
  <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                        @foreach (get_cached_products(4) as $key => $product)
                            <div class="carousel-box">
                                @include('frontend.'.get_setting('homepage_select').'.partials.product_box_1',['product' => $product])
                            </div>
                        @endforeach
                    </div>
    </div>
</div>

<div class="tab__content">
  <img src="{{ static_asset('uploads/all/AYx5MVuyoyplIL1EXTgcvquA9FM38UMBtOOxuRf0.webp') }}" class="img-fluid lazyload w-100">
  <div class="bg-white">
  <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                        @foreach (get_cached_products(6) as $key => $product)
                            <div class="carousel-box">
                                @include('frontend.'.get_setting('homepage_select').'.partials.product_box_1',['product' => $product])
                            </div>
                        @endforeach
                    </div>
    </div>
</div>


</div>
</div>
</div>

     <!-- Today's deal -->
    @php
        $todays_deal_section_bg = get_setting('todays_deal_section_bg_color');
    @endphp
    <div id="todays_deal" @if(get_setting('todays_deal_section_bg') == 1) style="background: {{ $todays_deal_section_bg }};" @endif>

    </div>

      <!-- Today's deal -->

    <div id="todays_deal2" @if(get_setting('todays_deal_section_bg') == 1) style="background: {{ $todays_deal_section_bg }};" @endif>

    </div>

  
<section class="mb-2 mb-md-3 mt-2 mt-md-3">
            <div class="container">
                <div class="bg-white">
                    <!-- Top Section -->
                    <div class="d-flex mt-2 mt-md-3 mb-2 mb-md-3 align-items-baseline justify-content-between">
                        <!-- Title -->
                        <h3 class="fs-16 fs-md-20 fw-700 mb-2 mb-sm-0">
                            <span class="">{{ translate('Featured Categories') }}</span>
                        </h3>
                    </div>
                </div>
                <!-- Categories -->
                <div class="bg-white px-sm-3">
                    <div class="aiz-carousel sm-gutters-17" data-items="4" data-xxl-items="4" data-xl-items="3.5"
                        data-lg-items="3" data-md-items="2" data-sm-items="2" data-xs-items="1" data-arrows="true"
                        data-dots="false" data-autoplay="false" data-infinite="true">
                       @foreach ($featured_categories as $key => $category)
                            @php
                                $category_name = $category->getTranslation('name');
                            @endphp
                            <div class="carousel-box position-relative p-0 has-transition border-right border-top border-bottom @if ($key == 0) border-left @endif">
                                <div class="h-200px h-sm-250px h-md-340px">
                                    <div class="h-100 w-100 w-xl-auto position-relative hov-scale-img overflow-hidden">
                                        <div class="position-absolute h-100 w-100 overflow-hidden">
                                            <img src="{{ isset($category->coverImage->file_name) ? my_asset($category->coverImage->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                                alt="{{ $category_name }}"
                                                class="img-fit h-100 has-transition"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                        </div>
                                        <div class="pb-4 px-4 absolute-bottom-left has-transition h-50 w-100 d-flex flex-column align-items-center justify-content-end"
                                            style="background: linear-gradient(to top, rgba(0,0,0,0.5) 50%,rgba(0,0,0,0) 100%) !important;">
                                            <div class="w-100">
                                                <a class="fs-16 fw-700 text-white animate-underline-white home-category-name d-flex align-items-center hov-column-gap-1"
                                                    href="{{ route('products.category', $category->slug) }}"
                                                    style="width: max-content;">
                                                    {{ $category_name }}&nbsp;
                                                    <i class="las la-angle-right"></i>
                                                </a>
                                                <div class="d-flex flex-wrap h-50px overflow-hidden mt-2">
                                                    @foreach ($category->childrenCategories->take(6) as $key => $child_category)
                                                    <a href="{{ route('products.category', $child_category->slug) }}" class="fs-13 fw-300 text-soft-light hov-text-white pr-3 pt-1">
                                                        {{ $child_category->getTranslation('name') }}
                                                    </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        @endforeach
                    </div>
                </div>
            </div>
        </section>



<div id="section_home_categories">

    </div>
<!-- 
@if (addon_is_activated('preorder'))
@include('preorder.frontend.home_page.thecore.newest_preorder')
@endif -->


<!-- Banner Section 2 -->
@php $homeBanner2Images = get_setting('home_banner2_images', null, $lang); @endphp
@if ($homeBanner2Images != null)
<div class="py-32px mt-2 mb-32px">
    <div class="container">
        @php
        $banner_2_imags = json_decode($homeBanner2Images);
        $data_md = count($banner_2_imags) >= 2 ? 2 : 1;
        $home_banner2_links = get_setting('home_banner2_links', null, $lang);
        @endphp
        <div class="aiz-carousel gutters-16 overflow-hidden arrow-inactive-none arrow-dark arrow-x-15"
            data-items="{{ count($banner_2_imags) }}" data-xxl-items="{{ count($banner_2_imags) }}"
            data-xl-items="{{ count($banner_2_imags) }}" data-lg-items="{{ $data_md }}"
            data-md-items="{{ $data_md }}" data-sm-items="1" data-xs-items="1" data-arrows="true"
            data-dots="false">
            @foreach ($banner_2_imags as $key => $value)
            <div class="carousel-box overflow-hidden hov-scale-img">
                <a href="{{ isset(json_decode($home_banner2_links, true)[$key]) ? json_decode($home_banner2_links, true)[$key] : '' }}"
                    class="d-block text-reset overflow-hidden ">
                    <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                        data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                        class="img-fluid lazyload w-100 has-transition"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- New Products -->
<div id="section_newest">
</div>
<div class="text-center d-none" id="view-more-container">
    <button type="button" class="btn btn-lg py-19px w-20 bg-light fs-16 my-32px" id="view-more-btn">
        {{ translate('Load More') }}
        <i id="spinner-icon" class="las la-lg la-spinner la-spin d-none"></i>
    </button>
</div>

@endsection

@section('script')
<script>
    // Countdown for mobile view
    function startSimpleCountdown(endDate) {
        function update() {
            const now = new Date();
            const diff = endDate - now;
            if (diff > 0) {
                const totalSeconds = Math.floor(diff / 1000);
                const days = Math.floor(totalSeconds / (60 * 60 * 24));
                const hours = Math.floor((totalSeconds % (60 * 60 * 24)) / (60 * 60));
                const mins = Math.floor((totalSeconds % (60 * 60)) / 60);
                const secs = totalSeconds % 60;

                document.getElementById("simple-days").textContent = days.toString().padStart(2, '0');
                document.getElementById("simple-hours").textContent = hours.toString().padStart(2, '0');
                document.getElementById("simple-mins").textContent = mins.toString().padStart(2, '0');
                document.getElementById("simple-secs").textContent = secs.toString().padStart(2, '0');
            } else {
                document.querySelector(".mobile-countdown-simple").textContent = "Sale ended";
                clearInterval(timer);
            }
        }

        update();
        const timer = setInterval(update, 1000);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const countdownEl = document.querySelector('.mobile-countdown-simple');
        if (!countdownEl) return;

        const endDateStr = countdownEl.dataset.endDate;
        if (endDateStr) {
            const parsedEndDate = new Date(endDateStr.replace(/-/g, '/'));
            startSimpleCountdown(parsedEndDate);
        }
    });



    let page = 1;        
    $(document).on('click', '#view-more-btn', function() {
        const $button = $(this);
        const originalText = $button.html(); 

        page++;
        $button.html('{{ translate("Loading...") }} <i id="spinner-icon" class="las la-lg la-spinner la-spin"></i>');
        $button.prop('disabled', true); 

        $.post('{{ route('home.section.newest_products') }}', {
            _token: '{{ csrf_token() }}',
            page: page
        }, function(data) {
            $button.prop('disabled', false);
            $button.html(originalText);
            
            if ($.trim(data) === '') {
                $button.prop('disabled', true).text('{{ translate("No More Products") }}');
            } else {
                $('#newest-products-list').append(data);
                AIZ.plugins.slickCarousel();
            }
        }).fail(function() {
            $button.prop('disabled', false);
            $button.html('{{ translate("Error, Try Again") }} <i id="spinner-icon" class="las la-lg la-spinner la-spin d-none"></i>');
        });
    });

    $(window).on('load', function() {
        $('.hot-category-box').addClass('d-flex flex-column justify-content-center align-items-center');
    });

    function toggleViewMoreButton() {
        if ($.trim($('#section_newest').html()).length > 0) {
            $('#view-more-container').removeClass('d-none').addClass('d-block');
        } else {
            $('#view-more-container').removeClass('d-block').addClass('d-none');
        }
    }

</script>
@endsection
