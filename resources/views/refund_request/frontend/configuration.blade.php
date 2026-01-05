@extends('seller.layouts.app')

@section('panel_content')
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-6 mb-4">
            <!-- Refund Type -->
            <div class="card shadow-none h-460px mb-0 h-100">
                <div class="card-body pb-5">
                    <div class="card-title text-primary fs-16 fw-600">
                        {{ translate('Refund Type & Days') }}
                    </div>
                    <hr>
                    <ul class="list-group">
                        @if (get_setting('refund_type') == 'global_refund')
                            <li class="d-flex justify-content-between align-items-center my-2 text-primary fs-13">
                                {{ translate('You are under the Global Refund type. Refund time : ') . ' ' . get_setting('refund_request_time') . ' ' . translate('days.') }}
                            </li>
                        @endif
                        @if (get_setting('refund_type') == 'category_based_refund')
                            <li class="d-flex justify-content-between align-items-center my-2 text-primary fs-13">
                                {{ translate('You are under the Category Based Refund type.') }}
                            </li>
                            <li class="d-flex justify-content-start align-items-center my-2 text-primary fs-13">
                                {{ translate('To see refund details, ') }}
                                <a class="btn btn-primary btn-xs ml-2" href="{{route('seller.categories_wise_product_refund')}}">
                                    {{ translate('click here.') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection