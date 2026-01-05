@if($product->brand->slug !== 'no_brand')
<div class="card-panel mb-4 mt-2 brand-card d-flex align-items-center">
    <a href="{{ route('products.brand', $product->brand->slug) }}" class="avatar-md mr-3 overflow-hidden rounded border brand-logo">
        <img class="lazyload h-100 w-100"
            src="{{ static_asset('assets/img/placeholder.jpg') }}"
            data-src="{{ uploaded_asset($product->brand->logo) }}"
            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
            alt="{{ $product->brand->name }}">
    </a>
    <div class="mt-1">
        <p class="mb-1 text-uppercase small text-muted">{{ translate('Brand') }}</p>
        <p class="mb-0 fw-700 text-dark">{{ $product->brand->name }}</p>
        <a class="small text-brand" href="{{route('products.brand', $product->brand->slug)}}">{{ translate('Products from this brand') }}</a>
    </div>
</div>
@endif
