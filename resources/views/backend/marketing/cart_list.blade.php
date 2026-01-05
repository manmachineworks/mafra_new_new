@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0">{{ translate('Cart List') }}</h5>
                    <small class="text-muted">{{ translate('Monitor active and abandoned carts and nudge customers to finish checkout.') }}</small>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Cart ID') }}</th>
                            <th>{{ translate('User Name') }}</th>
                            <th>{{ translate('Email') }}</th>
                            <th>{{ translate('Mobile Number') }}</th>
                            <th class="text-center">{{ translate('Total Items') }}</th>
                            <th class="text-right">{{ translate('Cart Value') }}</th>
                            <th>{{ translate('Last Product Added') }}</th>
                            <th>{{ translate('Cart Status') }}</th>
                            <th>{{ translate('Last Updated') }}</th>
                            <th class="text-right">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $cartItems = $user->carts;
                                $latestCart = $cartItems->first();
                                $totalItems = $cartItems->sum('quantity');
                                $cartValue = $cartItems->sum(fn($item) => $item->line_total ?? 0);
                                $isActive = $latestCart && $latestCart->updated_at && $latestCart->updated_at->gt(now()->subMinutes(30));
                                $lastProductName = optional(optional($latestCart)->product)->getTranslation('name') ?? translate('N/A');
                            @endphp
                            <tr>
                                <td>{{ optional($latestCart)->id ?? '—' }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email ?? translate('N/A') }}</td>
                                <td>{{ $user->phone ?? translate('N/A') }}</td>
                                <td class="text-center">{{ $totalItems }}</td>
                                <td class="text-right">{{ single_price($cartValue) }}</td>
                                <td class="text-truncate" style="max-width:200px;">{{ $lastProductName }}</td>
                                <td>
                                    <span class=" badge-pill badge-{{ $isActive ? 'success' : 'danger' }}">
                                        {{ $isActive ? translate('Active') : translate('Abandoned') }}
                                    </span>
                                </td>
                                <td>{{ optional(optional($latestCart)->updated_at)->diffForHumans() ?? translate('N/A') }}</td>
                                <td class="text-right">
                                    <button type="button"
                                        data-url="{{ $latestCart ? route('admin.cart-list.show', $latestCart) : '#' }}"
                                        class="btn btn-soft-primary btn-icon btn-circle btn-sm js-cart-details"
                                        @if(!$latestCart) disabled @endif
                                        title="{{ translate('View Details') }}">
                                        <i class="las la-eye"></i>
                                    </button>
                                    <button type="button"
                                        data-url="{{ $latestCart ? route('admin.cart-list.email', $latestCart) : '#' }}"
                                        class="btn btn-soft-info btn-icon btn-circle btn-sm js-cart-email"
                                        @if(!$latestCart) disabled @endif
                                        title="{{ translate('Send Email Reminder') }}">
                                        <i class="las la-envelope"></i>
                                    </button>
                                    <button type="button"
                                        data-url="{{ $latestCart ? route('admin.cart-list.sms', $latestCart) : '#' }}"
                                        class="btn btn-soft-success btn-icon btn-circle btn-sm js-cart-sms"
                                        @if(!$latestCart) disabled @endif
                                        title="{{ translate('Send SMS Reminder') }}">
                                        <i class="las la-sms"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">{{ translate('No carts found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
<div class="modal fade" id="cartDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ translate('Cart Details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="cart-detail-body">
                <div class="text-center text-muted py-5">{{ translate('Select a cart to see details.') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    (function() {
        "use strict";

        function handleAction(button, type) {
            const $btn = $(button);
            const url = $btn.data('url');
            if (!url || url === '#') {
                return;
            }

            $btn.prop('disabled', true);

            $.post(url, {
                _token: '{{ csrf_token() }}'
            }).done(function(response) {
                AIZ.plugins.notify('success', response.message || '{{ translate('Action completed') }}');
            }).fail(function(xhr) {
                const message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : '{{ translate('Unable to process the request.') }}';
                AIZ.plugins.notify('danger', message);
            }).always(function() {
                $btn.prop('disabled', false);
            });
        }

        $(document).on('click', '.js-cart-details', function() {
            const url = $(this).data('url');
            if (!url || url === '#') {
                return;
            }

            $('#cart-detail-body').html('<div class="text-center text-muted py-5">{{ translate('Loading cart details...') }}</div>');
            $('#cartDetailModal').modal('show');

            $.get(url)
                .done(function(response) {
                    $('#cart-detail-body').html(response.html || '');
                })
                .fail(function(xhr) {
                    const message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : '{{ translate('Unable to load cart details.') }}';
                    $('#cart-detail-body').html('<div class="text-center text-danger py-5">' + message + '</div>');
                });
        });

        $(document).on('click', '.js-cart-email', function() {
            handleAction(this, 'email');
        });

        $(document).on('click', '.js-cart-sms', function() {
            handleAction(this, 'sms');
        });
    })();
</script>
@endsection
