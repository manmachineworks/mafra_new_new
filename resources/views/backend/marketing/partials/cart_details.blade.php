<div class="mb-3">
    <h6 class="mb-2">{{ translate('User Details') }}</h6>
    <div class="row">
        <div class="col-md-4">
            <div class="fs-14 fw-600 text-dark">{{ $user->name }}</div>
            <div class="text-muted fs-13">{{ translate('User ID') }}: {{ $user->id }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-muted fs-13">{{ translate('Email') }}</div>
            <div class="fw-500">{{ $user->email ?? translate('N/A') }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-muted fs-13">{{ translate('Phone') }}</div>
            <div class="fw-500">{{ $user->phone ?? translate('N/A') }}</div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-borderless table-striped">
        <thead class="bg-light">
            <tr>
                <th>{{ translate('Product') }}</th>
                <th class="text-center">{{ translate('Qty') }}</th>
                <th class="text-right">{{ translate('Price') }}</th>
                <th class="text-right">{{ translate('Discount') }}</th>
                <th class="text-right">{{ translate('Subtotal') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cartItems as $item)
                <tr>
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            <span class="mr-2">
                                <img src="{{ uploaded_asset(optional($item->product)->thumbnail_img) }}"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                    class="img-fit size-40px rounded border" alt="{{ optional($item->product)->getTranslation('name') }}">
                            </span>
                            <div>
                                <div class="fw-600">{{ optional($item->product)->getTranslation('name') ?? translate('Product') }}</div>
                                @if($item->variation)
                                    <div class="text-muted fs-12">{{ translate('Variation') }}: {{ $item->variation }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="align-middle text-center">{{ $item->quantity }}</td>
                    <td class="align-middle text-right">{{ single_price(cart_product_price($item, $item->product, false, true)) }}</td>
                    <td class="align-middle text-right text-danger">-{{ single_price($item->discount ?? 0) }}</td>
                    <td class="align-middle text-right fw-600">{{ single_price($item->line_total ?? 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">{{ translate('No cart items found for this user.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="row justify-content-end mt-3">
    <div class="col-md-6">
        <div class="bg-light rounded p-3">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">{{ translate('Subtotal') }}</span>
                <span class="fw-600">{{ single_price($summary['subtotal'] ?? 0) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">{{ translate('Tax') }}</span>
                <span class="fw-600">{{ single_price($summary['taxTotal'] ?? 0) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">{{ translate('Shipping') }}</span>
                <span class="fw-600">{{ single_price($summary['shipping'] ?? 0) }}</span>
            </div>
            @if(($summary['discount'] ?? 0) > 0)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ translate('Discount') }}</span>
                    <span class="fw-600 text-success">-{{ single_price($summary['discount'] ?? 0) }}</span>
                </div>
            @endif
            @if(!empty($summary['couponCode']))
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ translate('Coupon') }}</span>
                    <span class="fw-600 text-success">{{ $summary['couponCode'] }}</span>
                </div>
            @endif
            <div class="d-flex justify-content-between pt-2 border-top">
                <span class="fw-700">{{ translate('Total Payable') }}</span>
                <span class="fw-700">{{ single_price($summary['total'] ?? 0) }}</span>
            </div>
        </div>
    </div>
</div>
