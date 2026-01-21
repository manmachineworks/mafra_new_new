@extends('auth.layouts.authentication')

@section('content')
    <!-- aiz-main-wrapper -->
    <div class="aiz-main-wrapper d-flex flex-column justify-content-md-center bg-white">
        <section class="bg-white overflow-hidden">
            <div class="row">
                <div class="col-xxl-6 col-xl-9 col-lg-10 col-md-7 mx-auto py-lg-4">
                    <div class="card shadow-lg rounded-lg border-0 overflow-hidden">
                        <div class="row no-gutters">
                            <!-- Left Side Image -->
                            <div class="col-lg-6 d-none d-lg-block bg-cover" 
                                 style="background-image: url('{{ uploaded_asset(get_setting('forgot_password_page_image')) }}'); min-height: 500px;">
                            </div>

                            <!-- Right Side Form -->
                            <div class="col-lg-6 p-4 p-md-5 d-flex flex-column justify-content-center border-left">
                                <!-- Site Icon -->
                                <div class="text-center mb-4">
                                    <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ translate('Site Icon')}}" style="height: 48px;">
                                </div>

                                <!-- Titles -->
                                <div class="text-center mb-4">
                                    <h1 class="h3 fw-700 text-primary mb-2">{{ translate('Forgot Password?') }}</h1>
                                    <p class="text-muted mb-0">
                                        {{ addon_is_activated('otp_system') ? 
                                            translate('Enter your email or phone to reset your password.') :
                                            translate('Enter your email to reset your password.') }}
                                    </p>
                                </div>

                                <!-- Method Toggle -->
                                @if (addon_is_activated('otp_system'))
                                <div class="d-flex justify-content-center mb-4">
                                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                        <label class="btn btn-outline-primary active" id="btn-email">
                                            <input type="radio" name="reset_method" value="email" checked onchange="toggleResetMethod('email')"> {{ translate('Email') }}
                                        </label>
                                        <label class="btn btn-outline-primary" id="btn-phone">
                                            <input type="radio" name="reset_method" value="phone" onchange="toggleResetMethod('phone')"> {{ translate('Phone') }}
                                        </label>
                                    </div>
                                </div>
                                @endif

                                <!-- Form -->
                                <form class="form-default" id="forgot-pass-form" role="form" action="{{ route('password.email') }}" method="POST">
                                    @csrf
                                    
                                    <!-- Hidden Inputs for Firebase -->
                                    <input type="hidden" name="firebase_id_token" id="firebase_id_token">
                                    <input type="hidden" name="firebase_verified_phone" id="firebase_verified_phone">
                                    <input type="hidden" name="country_code" value="">

                                    <!-- Email Input -->
                                    <div class="form-group email-group transition-3d-hover">
                                        <label class="form-label text-dark fw-600">{{ translate('Email Address') }}</label>
                                        <input type="email" class="form-control rounded-lg" name="email" id="email" 
                                               placeholder="{{ translate('johndoe@example.com') }}" 
                                               value="{{ old('email') }}" required>
                                        @if ($errors->has('email'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('email') }}</span>
                                        @endif
                                    </div>

                                    @if (addon_is_activated('otp_system'))
                                    <!-- Phone Input -->
                                    <div class="form-group phone-group d-none transition-3d-hover">
                                        <label class="form-label text-dark fw-600">{{ translate('Phone Number') }}</label>
                                        <input type="tel" id="phone-code" class="form-control rounded-lg" 
                                               placeholder="" name="phone" autocomplete="off">
                                        @if ($errors->has('phone'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('phone') }}</span>
                                        @endif
                                    </div>

                                    <!-- OTP Input (Hidden initially) -->
                                    <div class="form-group otp-group d-none mb-3">
                                        <label class="form-label text-dark fw-600">{{ translate('Verification Code') }}</label>
                                        <input type="text" class="form-control rounded-lg" id="otp_code" 
                                               placeholder="{{ translate('Enter 6-digit OTP') }}">
                                    </div>
                                    @endif

                                    <!-- Recaptcha Container (For Firebase) -->
                                    <div id="recaptcha-container"></div>

                                    <!-- New Password Fields (Hidden initially) -->
                                    <div class="password-group d-none">
                                        <div class="form-group">
                                            <label class="form-label text-dark fw-600">{{ translate('New Password') }}</label>
                                            <input type="password" class="form-control rounded-lg" name="password" id="new_password" 
                                                placeholder="{{ translate('New Password') }}" minlength="6">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-dark fw-600">{{ translate('Confirm Password') }}</label>
                                            <input type="password" class="form-control rounded-lg" name="password_confirmation" id="password_confirmation" 
                                                placeholder="{{ translate('Confirm Password') }}">
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="mb-3">
                                        <button type="submit" id="submit-btn" class="btn btn-primary btn-block btn-lg fw-600 rounded-lg transition-3d-hover shadow-sm">
                                            {{ translate('Send Reset Link') }}
                                        </button>
                                    </div>

                                    <div class="text-center">
                                        <a href="{{ url()->previous() }}" class="text-reset fw-600 fs-13">
                                            <i class="las la-arrow-left mr-1"></i>{{ translate('Back to Login') }}
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('script')
    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>

    <script type="text/javascript">
        // Firebase Config
        const firebaseConfig = {
            apiKey: "{{ env('FIREBASE_WEB_API_KEY') }}",
            authDomain: "{{ env('FIREBASE_WEB_AUTH_DOMAIN') }}",
            projectId: "{{ env('FIREBASE_WEB_PROJECT_ID') }}",
            storageBucket: "{{ env('FIREBASE_WEB_STORAGE_BUCKET') }}",
            messagingSenderId: "{{ env('FIREBASE_WEB_MESSAGING_SENDER_ID') }}",
            appId: "{{ env('FIREBASE_WEB_APP_ID') }}",
            measurementId: "{{ env('FIREBASE_WEB_MEASUREMENT_ID') }}"
        };

        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        const auth = firebase.auth();
        auth.languageCode = '{{ str_replace('_', '-', app()->getLocale()) }}';
        let confirmationResult = null;

        // Initialize Recaptcha
        window.onload = function() {
            window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                'size': 'invisible',
                'callback': (response) => {
                    // reCAPTCHA solved
                }
            });
        };

        // UI Toggles
        function toggleResetMethod(method) {
            const form = document.getElementById('forgot-pass-form');
            if (method === 'phone') {
                form.action = "{{ route('password.update.phone') }}";
                
                $('.email-group').addClass('d-none');
                $('#email').prop('disabled', true); // Disable to prevent validation
                $('#email').removeAttr('required');
                
                $('.phone-group').removeClass('d-none');
                $('#phone-code').prop('disabled', false);
                $('#phone-code').prop('required', true);
                
                // Hide Password Fields if logic specific
                $('.password-group').addClass('d-none');
                $('#new_password').prop('required', false);
                $('#password_confirmation').prop('required', false);

                $('#submit-btn').text("{{ translate('Get OTP') }}");
                $('#submit-btn').attr('type', 'button');
                $('#submit-btn').attr('onclick', 'handleOtpFlow()');
                
                $('#btn-email').removeClass('active');
                $('#btn-phone').addClass('active');
            } else {
                form.action = "{{ route('password.email') }}";

                $('.phone-group').addClass('d-none');
                $('#phone-code').prop('disabled', true);
                $('#phone-code').removeAttr('required');
                
                $('.email-group').removeClass('d-none');
                $('#email').prop('disabled', false);
                $('#email').attr('required', true);
                
                $('.otp-group').addClass('d-none'); 
                $('.password-group').addClass('d-none');
                
                $('#submit-btn').text("{{ translate('Send Reset Link') }}");
                $('#submit-btn').attr('type', 'submit');
                $('#submit-btn').removeAttr('onclick');
                
                $('#btn-phone').removeClass('active');
                $('#btn-email').addClass('active');
            }
        }

        // OTP Logic
        function handleOtpFlow() {
            if ($('.otp-group').hasClass('d-none')) {
                sendOTP();
            } else {
                verifyOTP();
            }
        }

        function sendOTP() {
            const phoneNumberInput = $('#phone-code').val();
            const countryCode = $('input[name=country_code]').val() || '91';
            
            if(!phoneNumberInput) {
                AIZ.plugins.notify('warning', "{{ translate('Please enter phone number') }}");
                return;
            }

            const fullPhoneNumber = "+" + countryCode + phoneNumberInput;
            
            $('#submit-btn').prop('disabled', true).text("{{ translate('Sending...') }}");

            auth.signInWithPhoneNumber(fullPhoneNumber, window.recaptchaVerifier)
                .then((result) => {
                    confirmationResult = result;
                    $('.otp-group').removeClass('d-none');
                    $('.phone-group').addClass('d-none'); // Lock phone
                    $('#submit-btn').text("{{ translate('Verify & Proceed') }}");
                    $('#submit-btn').prop('disabled', false);
                    AIZ.plugins.notify('success', "{{ translate('OTP Sent Successfully') }}");
                }).catch((error) => {
                    console.error("SMS Error:", error);
                    $('#submit-btn').prop('disabled', false).text("{{ translate('Get OTP') }}");
                    window.recaptchaVerifier.render().then(widgetId => grecaptcha.reset(widgetId));
                    AIZ.plugins.notify('danger', error.message);
                });
        }

        function verifyOTP() {
            const code = $('#otp_code').val();
            if (!code) {
                AIZ.plugins.notify('warning', "{{ translate('Please enter the OTP') }}");
                return;
            }

            $('#submit-btn').prop('disabled', true).text("{{ translate('Verifying...') }}");

            // [DEV ONLY] Bypass for testing without SMS
            @if(env('APP_ENV') == 'local')
            if (code === '271020') {
                console.log("Dev Bypass: Verifying with Magic OTP");
                const phone = "+$('input[name=country_code]').val()" + $('#phone-code').val();
                
                // Construct a fake JWT token: header.payload.signature
                const header = btoa(JSON.stringify({alg: "HS256", typ: "JWT"}));
                const payload = btoa(JSON.stringify({
                    sub: "dev-local-uid",
                    phone_number: "+" + $('input[name=country_code]').val() + $('#phone-code').val(),
                    user_id: "dev-local-uid"
                }));
                const signature = "fake-signature";
                const mockToken = `${header}.${payload}.${signature}`;

                onVerificationSuccess(mockToken, "+" + $('input[name=country_code]').val() + $('#phone-code').val());
                return;
            }
            @endif

            confirmationResult.confirm(code).then((result) => {
                const user = result.user;
                user.getIdToken().then((idToken) => {
                    onVerificationSuccess(idToken, user.phoneNumber);
                });
            }).catch((error) => {
                console.error("Verify Error:", error);
                $('#submit-btn').prop('disabled', false).text("{{ translate('Verify & Proceed') }}");
                AIZ.plugins.notify('danger', "{{ translate('Invalid OTP') }}");
            });
        }

        function onVerificationSuccess(idToken, phoneNumber) {
            // Set Hidden Fields
            $('#firebase_id_token').val(idToken);
            $('#firebase_verified_phone').val(phoneNumber);
            
            // Show Password Fields
            $('.otp-group').addClass('d-none');
            $('.password-group').removeClass('d-none');
            
            $('#new_password').prop('required', true);
            $('#password_confirmation').prop('required', true);

            // Update UI for Final Submit
            $('#submit-btn').prop('disabled', false).text("{{ translate('Reset Password') }}");
            $('#submit-btn').attr('type', 'submit'); // Revert to submit
            $('#submit-btn').removeAttr('onclick'); // Remove JS handler
            
            AIZ.plugins.notify('success', "{{ translate('Phone Verified! Set your new password.') }}");
        }
    </script>
    
    {{-- Removed missing partial include --}}
    @if (addon_is_activated('otp_system'))
        <script>
            // Initialize Country Code Input (reusing existing logic from other pages)
            var input = document.querySelector("#phone-code");
            var iti = intlTelInput(input, {
                separateDialCode: true,
                utilsScript: "{{ static_asset('assets/js/intlTelutils.js') }}?1590403638580",
                onlyCountries: @php echo json_encode(\App\Models\Country::where('status', 1)->pluck('code')->toArray()) @endphp,
                customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
                    if (selectedCountryData.iso2 == 'bd') { return "01xxxxxxxxx"; }
                    return selectedCountryPlaceholder;
                }
            });
            var country = iti.getSelectedCountryData();
            $('input[name=country_code]').val(country.dialCode);

            input.addEventListener("countrychange", function(e) {
                var country = iti.getSelectedCountryData();
                $('input[name=country_code]').val(country.dialCode);
            });
        </script>
    @endif
@endsection