@extends('backend.layouts.app')

@section('content')

    @php
        $isCategoryBasedRefund = get_setting('refund_type') == 'global_refund';
    @endphp

    <div class="row">
        <!-- Left Side: Refund Type, Set Refund Time, Set Refund Sticker -->
        <div class="col-lg-7">
            <!-- Refund Type -->
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0 h6 text-center">{{ translate('Refund Type') }}</h3>
                </div>
                <form class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="types[]" value="refund_type">
                    <div class="card-body">
                        <div class="radio mar-btm">
                            <input id="global_refund" class="magic-radio" type="radio" name="refund_type"
                                value="global_refund" {{ get_setting('refund_type') == 'global_refund' ? 'checked' : '' }}>
                            <label for="global_refund" class="fs-13">{{ translate('Global Refund') }}</label>
                        </div>
                        <div class="radio mar-btm">
                            <input id="category_based_refund" class="magic-radio" type="radio" name="refund_type"
                                value="category_based_refund" {{ get_setting('refund_type') == 'category_based_refund' ? 'checked' : '' }}>
                            <label for="category_based_refund"
                                class="fs-13">{{ translate('Category Based Refund') }}</label>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Set Refund Time (Conditionally shown) -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Set Refund Time') }}</h5>
                </div>
                <form class="form-horizontal" action="{{ route('refund_request_time_config') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">
                            <input type="hidden" name="type" value="refund_request_time">
                            <label
                                class="col-lg-4 col-form-label">{{ translate('Set Time for sending Refund Request') }}</label>
                            <div class="col-lg-5">
                                <input type="number" min="0" {{ $isCategoryBasedRefund ? '' : 'disabled' }} step="1" value="{{ get_setting('refund_request_time') }}"
                                    name="value" class="form-control" placeholder="Days">
                            </div>
                            <div class="col-lg-3 d-flex align-items-center">
                                {{ translate('days') }}
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" {{ $isCategoryBasedRefund ? '' : 'disabled' }} class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Set Refund Sticker (Conditionally shown) -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 h6">{{ translate('Set Refund Sticker') }}</h6>
                </div>
                <form class="form-horizontal" action="{{ route('refund_sticker_config') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">
                            <input type="hidden" name="type" value="refund_sticker">
                            <label class="col-md-2 col-form-label">{{ translate('Sticker') }}</label>
                            <div class="col-md-10">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ translate('Browse') }}
                                        </div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="logo" class="selected-files"
                                        value="{{ get_setting('refund_sticker') }}">
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side: Note -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Note') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item text-muted">
                            1. {{ translate('If the Refund Type is Global') }}
                            {{ translate('then set Refund Time') }}.
                        </li>
                        <li class="list-group-item text-muted">
                            2. {{ translate('If the Refund Type is Category Based, set') }}
                            <a href="{{ route('categories_wise_product_refund') }}">{{ translate('Here') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')

@endsection