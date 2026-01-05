@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ translate('Add Prepaid Discount Rule') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.prepaid-discounts.store') }}" method="POST">
                    @csrf
                    @include('backend.marketing.prepaid_discounts.partials.form', ['prepaid_discount' => null])
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">{{ translate('Create') }}</button>
                        <a href="{{ route('admin.prepaid-discounts.index') }}" class="btn btn-secondary">{{ translate('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
