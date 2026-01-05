<div class="card-panel mt-2">
    @if($product->is_cod)
    <div class="fw-16 mt-2">
        <p class="text-capitalize fs-16 fw-700 text-dark">{{translate('Cash on Delivery')}}</p>
    </div>
    <div class="status-pill warning mb-2">
        <i class="las la-check-circle mr-2"></i> {{translate('Cash on Delivery Available')}}
    </div>
    <div class="mt-1">

        @if($product->is_prepayment)
        <p class="text-muted mb-1"><b>{{translate('Prepayment needed for cash on delivery')}}</b></p>
        <p class="text-muted mb-2"><b>{{translate('Pay only '. $product->preorder_prepayment?->prepayment_amount .' to avail Cash on Delivery')}}</b></p>
        @endif
        <p class="text-muted mb-0">{{ $product->preorder_cod?->note?->description }}</p>
    </div>
    @endif
</div>
