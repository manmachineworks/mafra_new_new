<div class="card-panel mb-4 mt-4 refund-card">
    <div class="section-wrapper mt-1">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <p class="fs-16 fw-700 text-uppercase mb-0 text-dark">{{translate('Refund')}}</p>
            <i class="las la-info-circle fs-16 text-muted"></i>
        </div>
        <div class="status-pill info mb-2">
            <i class="las la-check mr-2"></i> {{translate('Refund Available for this product')}}
        </div>
        <div class="mt-2">
            @if($product->preorder_refund?->note?->description != null && $product->preorder_refund->show_refund_note)
            <p id="text-{{ $product->preorder_refund?->note?->id }}" class="text-muted fs-14">
                <span id="short-text-{{ $product->preorder_refund?->note?->id }}">
                    {{ Str::limit($product->preorder_refund?->note?->description, 100) }} 
                </span>
                <span class="d-none text-muted fs-14" id="full-text-{{ $product->preorder_refund?->note?->id }}">{{ $product->preorder_refund?->note?->description }}</span>
                @if (strlen($product->preorder_refund?->note?->description) > 100)
                <a href="javascript:void(0);" class="text-brand" onclick="toggleText({{ $product->preorder_refund?->note?->id }})" id="toggle-link-{{ $product->preorder_refund?->note?->id }}">{{translate('See More')}}</a>
                @endif
            </p>
            @endif
        </div>
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
