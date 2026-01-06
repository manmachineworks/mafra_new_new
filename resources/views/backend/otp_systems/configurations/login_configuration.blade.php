@extends('backend.layouts.app')

@section('content')
    
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ translate('Login With OTP') }}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'login_with_otp')"
                        <?php if (get_setting('login_with_otp') == 1) {
                            echo 'checked';
                        } ?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="mb-0 h6">{{ translate('Firebase Phone OTP') }}</h3>
                    <small class="text-muted">{{ translate('Requires Firebase config keys in .env') }}</small>
                </div>
                <img src="https://www.gstatic.com/mobilesdk/160503_mobilesdk/logo/2x/firebase_96dp.png" alt="Firebase" style="height:32px;">
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>{{ translate('Enable Firebase OTP') }}</div>
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'firebase_otp_enabled')" {{ get_setting('firebase_otp_enabled') == 1 ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>{{ translate('Require OTP on Login') }}</div>
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'firebase_otp_require_login')" {{ get_setting('firebase_otp_require_login') == 1 ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>{{ translate('Require OTP on Registration') }}</div>
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'firebase_otp_require_registration')" {{ get_setting('firebase_otp_require_registration') == 1 ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>{{ translate('Require OTP on Forgot Password') }}</div>
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'firebase_otp_require_forgot')" {{ get_setting('firebase_otp_require_forgot') == 1 ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function updateSettings(el, type) {

            if('{{env('DEMO_MODE')}}' == 'On'){
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                return;
            }
            
            var value = ($(el).is(':checked')) ? 1 : 0;
             
            $.post('{{ route('business_settings.update.activation') }}', {
                _token: '{{ csrf_token() }}',
                type: type,
                value: value
            }, function(data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ translate('Settings updated successfully') }}');
                } else {
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
    </script>
@endsection


