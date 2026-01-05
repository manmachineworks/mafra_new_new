<div class="card-panel mb-4 mt-4 shipping-card">
    <div class="shipping-section">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <p class="text-uppercase fs-16 fw-700 mb-0 text-dark">{{translate('Shipping')}}</p>
            <i class="las la-info-circle fs-16 text-muted"></i>
        </div>

        @if($product->preorder_shipping?->shipping_type == 'free')
        <div class="status-pill success mb-3">
            <i class="las la-check mr-2"></i> {{translate('Free Shipping')}}
        </div>
        @endif

        <div class="mt-2">
            @if($product->preorder_shipping?->show_shipping_time)
                <p class="mb-1 text-dark">{{translate('Estimated Shipping Time :')}} <b>{{ $product->preorder_shipping->min_shipping_days }} -  {{ $product->preorder_shipping->max_shipping_days }}  {{translate('days')}}</b></p>
            @endif

            @if($product->preorder_shipping?->note?->description != null && $product->preorder_shipping->show_shipping_note)
                <p class="text-muted fs-14 mb-2">
                    <span id="short-text-{{ $product->preorder_shipping?->note?->id }}">
                        {{ Str::limit($product->preorder_shipping?->note?->description, 100) }}
                    </span>
                    <span class="d-none" id="full-text-{{ $product->preorder_shipping?->note?->id }}">
                        {{ $product->preorder_shipping?->note?->description }}
                    </span>
                    @if (strlen($product->preorder_cod?->note?->description) > 100)
                    <a href="javascript:void(0);" class="text-brand" onclick="toggleText({{ $product->preorder_shipping?->note?->id }})" id="toggle-link-{{ $product->preorder_shipping?->note?->id }}">
                        {{ translate('See More') }}
                    </a>
                    @endif
                </p>
            @endif
            @if(get_setting('product_query_activation') == 1)
                <p class="text-muted fs-14 mb-0"><a href="#product_query" class="text-brand">{{translate('Contact Us')}}</a> {{translate('for shipping time for larger orders.')}}</p>
            @endif
        </div>
    </div>


    <div class="cod mt-4">
        @if($product->is_cod)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <p class="text-uppercase fs-16 fw-700 mb-0 text-dark">{{translate('Cash on Delivery')}}</p>
            <i class="las la-info-circle fs-16 text-muted"></i>
        </div>
        <div class="status-pill warning mb-2">
            <i class="las la-check mr-2"></i> {{translate('Cash on Delivery Available')}}
        </div>
        <div class="mt-1">
            @if($product->is_prepayment)
            <p class="text-muted mt-2 fs-14 mb-1">{{translate('Prepayment needed for cash on delivery')}}</p>
            <p class="text-muted fs-14">{{translate('Pay only')}} {{format_price($product->preorder_prepayment?->prepayment_amount)}} {{translate('to avail Cash on Delivery')}}</p>
            @endif

            @if($product->preorder_cod?->note?->description != null && $product->preorder_cod?->show_cod_note)
                <p class="text-muted fs-14">
                    <span id="short-text-{{ $product->preorder_cod?->note?->id }}">
                        {{ Str::limit($product->preorder_cod?->note?->description, 100) }}
                    </span>
                    <span class="d-none" id="full-text-{{ $product->preorder_cod?->note?->id }}">
                        {{ $product->preorder_cod?->note?->description }}
                    </span>
                    @if (strlen($product->preorder_cod?->note?->description) > 100)
                    <a href="javascript:void(0);" class="text-brand" onclick="toggleText({{ $product->preorder_cod?->note?->id }})" id="toggle-link-{{ $product->preorder_cod?->note?->id }}">
                        {{ translate('See More') }}
                    </a>
                    @endif

                </p>
            @endif

        </div>
        @endif
    </div>
</div>

<script>
function toggleText(id) {
    const shortText = document.getElementById(`short-text-${id}`);
    const fullText = document.getElementById(`full-text-${id}`);
    const toggleLink = document.getElementById(`toggle-link-${id}`);

    if (fullText.classList.contains('d-none')) {
        shortText.classList.add('d-none'); 
        fullText.classList.remove('d-none'); 
        toggleLink.textContent = 'See Less'; 
    } else {
        shortText.classList.remove('d-none'); 
        fullText.classList.add('d-none'); 
        toggleLink.textContent = 'See More'; 
    }
}
    
</script>
