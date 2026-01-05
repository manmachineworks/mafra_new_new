@extends('frontend.layouts.user_panel')

@section('panel_content')

{{-- Theme-only UI/UX improvements (NO layout change) --}}
@section('style')
<style>
    :root{
        --brand:#c70a0a;
        --dark:#212121;
        --white:#ffffff;

        --bg:#f6f7fb;
        --muted:#6b7280;
        --border:#e5e7eb;

        --radius:16px;
        --radius-sm:12px;
        --shadow:0 10px 28px rgba(17,24,39,.10);
        --shadow-sm:0 6px 16px rgba(17,24,39,.08);
    }

    /* Card shell */
    .ph-card{
        background: var(--white);
        border: 1px solid var(--border) !important;
        border-radius: var(--radius) !important;
        box-shadow: var(--shadow-sm);
    }

    /* Title */
    .ph-title{
        color: var(--dark) !important;
        font-weight: 900 !important;
        letter-spacing: .2px;
    }

    /* Tabs (keep same markup, just restyle) */
    .purchase-history-tab.nav-tabs{
        gap: 6px;
    }
    .purchase-history-tab.nav-tabs .nav-item{
        margin-bottom: 0 !important;
    }
    .purchase-history-tab.nav-tabs .nav-link{
        border: 1px solid transparent !important;
        background: transparent !important;
        color: rgba(33,33,33,.78) !important;
        font-weight: 800;
        padding: 10px 12px;
        border-radius: 999px !important;
        transition: all .15s ease;
        line-height: 1;
    }
    .purchase-history-tab.nav-tabs .nav-link:hover{
        background: rgba(199,10,10,.06) !important;
        border-color: rgba(199,10,10,.18) !important;
        color: var(--dark) !important;
        transform: translateY(-1px);
    }
    .purchase-history-tab.nav-tabs .nav-link.active{
        background: var(--brand) !important;
        border-color: var(--brand) !important;
        color: #fff !important;
        box-shadow: 0 10px 18px rgba(199,10,10,.18);
    }

    /* Divider */
    .ph-divider{
        border-bottom: 1px solid var(--border) !important;
    }

    /* Filter select (bootstrap-select / selectpicker friendly) */
    .purchase-history.form-control,
    .purchase-history .btn.dropdown-toggle{
        border-radius: 999px !important;
        border: 1px solid var(--border) !important;
        background: var(--white) !important;
        color: var(--dark) !important;
        font-weight: 800;
        height: 40px;
        padding: 8px 14px;
        box-shadow: none !important;
    }
    .purchase-history .btn.dropdown-toggle:hover{
        border-color: rgba(199,10,10,.35) !important;
    }
    .purchase-history .btn.dropdown-toggle:focus,
    .purchase-history .btn.dropdown-toggle:active{
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(199,10,10,.12) !important;
        border-color: rgba(199,10,10,.55) !important;
    }

    /* Tab content container feel (no structural change) */
    #tab-content{
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--white);
        box-shadow: 0 6px 16px rgba(17,24,39,.05);
        min-height: 120px;
    }

    /* Pagination links inside AJAX-loaded content (best-effort) */
    .pagination .page-link{
        border-radius: 12px !important;
        border-color: var(--border) !important;
        color: var(--dark) !important;
        font-weight: 800;
        margin: 0 3px;
    }
    .pagination .page-item.active .page-link{
        background: var(--brand) !important;
        border-color: var(--brand) !important;
        color: #fff !important;
    }
    .pagination .page-link:hover{
        border-color: rgba(199,10,10,.35) !important;
        background: rgba(199,10,10,.06) !important;
    }

    /* Modal buttons (cancel confirmation) */
    #delete-modal .modal-content{
        border-radius: var(--radius) !important;
        border: 1px solid var(--border) !important;
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    #delete-modal .modal-header{
        border-bottom: 1px solid var(--border) !important;
        background: var(--white);
    }
    #delete-modal .modal-title{
        color: var(--dark);
        font-weight: 900;
    }
    #delete-modal .btn.btn-primary{
        background: var(--brand) !important;
        border-color: var(--brand) !important;
        color: #fff !important;
        font-weight: 900;
    }
    #delete-modal .btn.btn-secondary{
        background: rgba(33,33,33,.08) !important;
        border-color: rgba(33,33,33,.08) !important;
        color: var(--dark) !important;
        font-weight: 900;
    }
</style>
@endsection

<div class="card shadow-none rounded-0 border p-4 ph-card">
    <h5 class="mb-2 fs-20 fw-700 text-dark ph-title">{{ translate('Purchase History') }}</h5>

    <!-- Tabs & Filters -->
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 ph-divider">
        <ul class="nav nav-tabs purchase-history-tab border-0 fs-12 ml-n3" id="orderTabs">
            @foreach (['All', 'Unpaid', 'Confirmed', 'Picked_Up', 'Delivered', 'To Review'] as $status)
            <li class="nav-item">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                    onclick="changeTab(this, '{{ Str::slug($status) }}')">
                    {{ translate($status) }}
                </button>
            </li>
            @endforeach
        </ul>

        <div class="form-group mb-0 w-25">
            <select class="form-control aiz-selectpicker purchase-history" name="delivery_status" id="delivery_status"
                data-style="btn-light" data-width="100%">
                <option value="">{{ translate('All') }}</option>
                <option value="pending" {{ request('delivery_status') == 'pending' ? 'selected' : '' }}>{{ translate('Pending') }}</option>
                <option value="on_the_way" {{ request('delivery_status') == 'on_the_way' ? 'selected' : '' }}>{{ translate('On The Way') }}</option>
                <option value="delivered" {{ request('delivery_status') == 'delivered' ? 'selected' : '' }}>{{ translate('Delivered') }}</option>
                <option value="cancelled" {{ request('delivery_status') == 'cancelled' ? 'selected' : '' }}>{{ translate('Cancelled') }}</option>
            </select>
        </div>
    </div>

    <!-- Dynamic Tab Content -->
    <div class="tab-content mt-4" id="orderTabContent">
        <div class="tab-pane fade show active p-3 p-md-4" id="tab-content">
            <!-- AJAX content will load here -->
        </div>
    </div>
</div>
@endsection

@section('modal')
<!-- Product Review Modal -->
<div class="modal fade" id="product-review-modal">
    <div class="modal-dialog">
        <div class="modal-content" id="product-review-modal-content"></div>
    </div>
</div>

<!-- Delete modal -->
<div id="delete-modal" class="modal fade">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{translate('Cancel Confirmation')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mt-1 fs-14" style="color: rgba(33,33,33,.78); font-weight: 700;">
                    {{translate('Are you sure to Cancel this Order?')}}
                </p>
                <button type="button" class="btn btn-secondary rounded-5 mt-2 btn-sm px-3" data-dismiss="modal">
                    {{translate('No')}}
                </button>
                <a href="" id="delete-link" class="btn btn-primary rounded-5 mt-2 btn-sm px-3">
                    {{translate('Yes')}}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    let currentTab = 'all';

    function getOrderData(slug, page = 1) {
        currentTab = slug;
        $.ajax({
            url: `{{ route('purchase_history.filter') }}?page=${page}`,
            method: 'GET',
            data: { tab: slug.replace(/-/g, '_') },
            success: function(response) {
                $('#tab-content').html(response.html);
            },
            error: function() {
                $('#tab-content').html('<div class="text-danger p-4">{{ translate("Failed to load data.") }}</div>');
            }
        });
    }

    function changeTab(button, statusSlug) {
        document.querySelectorAll('#orderTabs .nav-link').forEach(el => el.classList.remove('active'));
        button.classList.add('active');
        getOrderData(statusSlug);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const deliverySelect = document.getElementById('delivery_status');

        function loadOrdersByStatus(status) {
            getOrderData(status);
        }

        deliverySelect.addEventListener('change', function() {
            loadOrdersByStatus(this.value || 'all');
            document.querySelectorAll('#orderTabs .nav-link').forEach(el => el.classList.remove('active'));
        });

        const urlParams = new URLSearchParams(window.location.search);
        const toReviewParam = urlParams.get('to_review');

        if (toReviewParam && (toReviewParam === '1')) {
            const toReviewBtn = document.querySelector(`#orderTabs button[onclick*="to-review"]`);
            if (toReviewBtn) {
                document.querySelectorAll('#orderTabs .nav-link').forEach(el => el.classList.remove('active'));
                toReviewBtn.classList.add('active');
                getOrderData('to-review');
            }
        } else {
            loadOrdersByStatus('all');
        }
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        getOrderData(currentTab, page);
    });

    function product_review(product_id,order_id) {
        $.post(`{{ route('product_review_modal') }}`, {
            _token: '{{ @csrf_token() }}',
            product_id: product_id,
            order_id: order_id
        }, function(data) {
            $('#product-review-modal-content').html(data);
            $('#product-review-modal').modal('show', { backdrop: 'static' });
            AIZ.extra.inputRating();
        });
    }

    $(document).on('click', '.confirm-delete', function (e) {
        e.preventDefault();
        let url = $(this).data('href');
        $('#delete-link').attr('href', url);
        $('#delete-modal').modal('show');
    });
</script>
@endsection
