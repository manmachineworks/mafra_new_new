@php
    $phonepeVersion = get_setting('phonepe_version', '1');
@endphp
<form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="phonepe">

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ translate('Version') }}</label>
        </div>
        <div class="col-md-8">
            <div class="form-check form-check-inline">
                <input class="form-check-input " type="radio" name="phonepe_version" id="version1" value="1"
                        @if ($phonepeVersion === '1') checked @endif>
                <label class="form-check-label" for="version1">V1</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input ml-4" type="radio" name="phonepe_version" id="version2" value="2"
                    @if ($phonepeVersion === '2') checked @endif>
                <label class="form-check-label" for="version2">V2</label>
            </div>
        </div>
    </div>


    <div class="v1-fields">

        <div class="form-group row">
            <input type="hidden" name="types[]" value="PHONEPE_MERCHANT_ID">
            <div class="col-lg-4">
                <label class="col-from-label">{{ translate('Merchant Id') }}</label>
            </div>
            <div class="col-lg-8">
                <input type="text" class="form-control" name="PHONEPE_MERCHANT_ID"
                    value="{{ env('PHONEPE_MERCHANT_ID') }}"
                    placeholder="{{ translate('PHONEPE MERCHANT ID') }}" required>
            </div>
        </div>

        <div class="form-group row">
            <input type="hidden" name="types[]" value="PHONEPE_SALT_KEY">
            <div class="col-lg-4">
                <label class="col-from-label">{{ translate('PHONEPE_SALT KEY') }}</label>
            </div>
            <div class="col-lg-8">
                <input type="text" class="form-control" name="PHONEPE_SALT_KEY"
                    value="{{ env('PHONEPE_SALT_KEY') }}"
                    placeholder="{{ translate('PHONEPESALT KEY') }}" required>
            </div>
        </div>

        <div class="form-group row">
            <input type="hidden" name="types[]" value="PHONEPE_SALT_INDEX">
            <div class="col-lg-4">
                <label class="col-from-label">{{ translate('PHONEPE SALT INDEX') }}</label>
            </div>
            <div class="col-lg-8">
                <input type="text" class="form-control" name="PHONEPE_SALT_INDEX"
                    value="{{ env('PHONEPE_SALT_INDEX') }}"
                    placeholder="{{ translate('PHONEPE SALT INDEX') }}" required>
            </div>
        </div>
    </div>


    <div class="v2-fields">
        <div class="form-group row">
            <input type="hidden" name="types[]" value="PHONEPE_CLIENT_ID">
            <div class="col-lg-4">
                <label class="col-from-label">{{ translate('PHONEPE CLIENT ID') }}</label>
            </div>
            <div class="col-lg-8">
                <input type="text" class="form-control" name="PHONEPE_CLIENT_ID"
                    value="{{ env('PHONEPE_CLIENT_ID') }}"
                    placeholder="{{ translate('PHONEPE CLIENT ID') }}" required>
            </div>
        </div>

        <div class="form-group row">
            <input type="hidden" name="types[]" value="PHONEPE_CLIENT_SECRET">
            <div class="col-lg-4">
                <label class="col-from-label">{{ translate('PHONEPE CLIENT SECRET') }}</label>
            </div>
            <div class="col-lg-8">
                <input type="text" class="form-control" name="PHONEPE_CLIENT_SECRET"
                    value="{{ env('PHONEPE_CLIENT_SECRET') }}"
                    placeholder="{{ translate('PHONEPE CLIENT SECRET') }}" required>
            </div>
        </div>

        <div class="form-group row">
            <input type="hidden" name="types[]" value="PHONEPE_CLIENT_VERSION">
            <div class="col-lg-4">
                <label class="col-from-label">{{ translate('PHONEPE CLIENT VERSION') }}</label>
            </div>
            <div class="col-lg-8">
                <input type="text" class="form-control" name="PHONEPE_CLIENT_VERSION"
                    value="{{ env('PHONEPE_CLIENT_VERSION') }}"
                    placeholder="{{ translate('PHONEPE CLIENT VERSION') }}" required>
            </div>
        </div>
    </div>

    

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Sandbox Mode') }}</label>
        </div>
        <div class="col-md-8">
            <label class="aiz-switch aiz-switch-success mb-0">
                <input value="1" name="phonepe_sandbox" type="checkbox"
                    @if (get_setting('phonepe_sandbox') == 1) checked @endif>
                <span class="slider round"></span>
            </label>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
    </div>
</form>