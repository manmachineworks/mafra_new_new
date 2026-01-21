@extends('auth.layouts.authentication')

@section('content')
    <div class="aiz-main-wrapper d-flex flex-column justify-content-center bg-white" style="min-height: 100vh;">
        <section class="h-100">
            <div class="container-fluid h-100 p-0">
                <div class="row no-gutters h-100 align-items-center">
                    <!-- Left Side: Image -->
                    <div class="col-lg-6 d-none d-lg-block h-100"
                        style="background-image: url('{{ uploaded_asset(get_setting('customer_login_page_image')) }}'); background-size: cover; background-position: center;">
                        <div class="h-100 w-100 position-relative" style="background: rgba(0,0,0,0.3);">
                            <div class="position-absolute" style="bottom: 50px; left: 50px; right: 50px;">
                                <div class="text-white">
                                    <h1 class="fw-700 fs-40 mb-3">{{ translate('Welcome Back!') }}</h1>
                                    <p class="fs-18 opacity-80">
                                        {{ translate('Login to access your personalized dashboard, track orders, and more.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Login Form -->
                    <div class="col-lg-6 bg-white h-100 d-flex flex-column justify-content-center py-5">
                        <div class="mx-auto w-100" style="max-width: 480px; padding: 20px;">

                            <!-- Site Icon & Mobile Header -->
                            <div class="text-center mb-4">
                                <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ translate('Site Icon')}}"
                                    class="mb-3" style="height: 50px;">
                                <h2 class="fs-24 fw-700 text-dark">{{ translate('Login to your account') }}</h2>
                                <p class="text-muted fs-14">{{ translate('Enter your details to proceed') }}</p>
                            </div>

                            <!-- Login Type Tabs -->
                            <div class="d-flex justify-content-center mb-4 border-bottom">
                                <button type="button"
                                    class="btn btn-link text-decoration-none pb-2 border-bottom border-primary border-width-2 fw-700 text-primary"
                                    id="tab-password" onclick="switchLoginMode('password')">
                                    {{ translate('Password Login') }}
                                </button>
                                <button type="button" class="btn btn-link text-decoration-none pb-2 fw-600 text-muted"
                                    id="tab-otp" onclick="switchLoginMode('otp')">
                                    {{ translate('OTP Login') }}
                                </button>
                            </div>

                            <form class="form-default loginForm" id="user-login-form" role="form"
                                action="{{ route('login') }}" method="POST">
                                @csrf

                                <!-- Identifier Toggle (Email/Phone) -->
                                <div class="form-group text-center mb-4">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary active"
                                            id="btn-use-phone"
                                            onclick="toggleEmailPhone('phone')">{{ translate('Phone') }}</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-use-email"
                                            onclick="toggleEmailPhone('email')">{{ translate('Email') }}</button>
                                    </div>
                                </div>

                                <!-- Phone Input -->
                                <div class="form-group phone-form-group">
                                    <label class="fs-12 fw-700 text-soft-dark">{{ translate('Phone Number') }}</label>
                                    <input type="tel" id="phone-code" class="form-control rounded-0"
                                        value="{{ old('phone') }}" placeholder="" name="phone" autocomplete="off">
                                    <input type="hidden" name="country_code" value="">
                                </div>

                                <!-- Email Input -->
                                <div class="form-group email-form-group d-none">
                                    <label class="fs-12 fw-700 text-soft-dark">{{ translate('Email Address') }}</label>
                                    <input type="email"
                                        class="form-control rounded-0 {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        value="{{ old('email') }}" placeholder="{{ translate('johndoe@example.com') }}"
                                        name="email" id="email" autocomplete="off">
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback"><strong>{{ $errors->first('email') }}</strong></span>
                                    @endif
                                </div>

                                <!-- Password Input -->
                                <div class="form-group password-login-block">
                                    <label class="fs-12 fw-700 text-soft-dark">{{ translate('Password') }}</label>
                                    <div class="position-relative">
                                        <input type="password"
                                            class="form-control rounded-0 {{ $errors->has('password') ? ' is-invalid' : '' }}"
                                            placeholder="{{ translate('Enter your password') }}" name="password"
                                            id="password">
                                        <i class="password-toggle las la-eye position-absolute"
                                            style="top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer;"></i>
                                    </div>
                                    <div class="text-right mt-2">
                                        <a href="{{ route('password.request') }}"
                                            class="fs-12 text-primary">{{ translate('Forgot Password?') }}</a>
                                    </div>
                                </div>

                                <!-- OTP Input (Hidden by default) -->
                                <div class="form-group otp-form-group d-none">
                                    <label class="fs-12 fw-700 text-soft-dark">{{ translate('Verification Code') }}</label>
                                    <input type="text" class="form-control rounded-0" value=""
                                        placeholder="{{ translate('Enter 6-digit OTP') }}" name="otp_code" id="otp_code"
                                        autocomplete="off" maxlength="6">
                                    <small
                                        class="text-muted">{{ translate('Check your phone/email for the code.') }}</small>
                                </div>

                                <!-- Hidden Fields for Firebase/Logic -->
                                <input type="hidden" name="firebase_id_token" id="firebase_id_token">
                                <input type="hidden" name="firebase_verified_phone" id="firebase_verified_phone">
                                <div id="recaptcha-container"></div>

                                <!-- Remember Me -->
                                <div class="form-group mb-4">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class="fs-14 text-dark">{{ translate('Remember Me') }}</span>
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit"
                                    class="btn btn-primary btn-block fw-700 fs-16 rounded-0 submit-button shadow-sm">
                                    {{ translate('Login') }}
                                </button>
                            </form>

                            <!-- Social Login -->
                            @if(get_setting('google_login') == 1 || get_setting('facebook_login') == 1 || get_setting('twitter_login') == 1 || get_setting('apple_login') == 1)
                                <div class="text-center my-4">
                                    <span class="bg-white fs-12 text-gray px-2"
                                        style="position: relative; z-index: 1;">{{ translate('Or Login With') }}</span>
                                    <div style="height: 1px; background: #eee; margin-top: -10px;"></div>
                                </div>
                                <ul class="list-inline social colored text-center mb-4">
                                    @if (get_setting('facebook_login') == 1)
                                        <li class="list-inline-item"><a
                                                href="{{ route('social.login', ['provider' => 'facebook']) }}"
                                                class="facebook shadow-sm"><i class="lab la-facebook-f"></i></a></li>
                                    @endif
                                    @if (get_setting('twitter_login') == 1)
                                        <li class="list-inline-item"><a
                                                href="{{ route('social.login', ['provider' => 'twitter']) }}"
                                                class="x-twitter shadow-sm"><i class="lab la-twitter"></i></a></li>
                                    @endif
                                    @if(get_setting('google_login') == 1)
                                        <li class="list-inline-item"><a href="{{ route('social.login', ['provider' => 'google']) }}"
                                                class="google shadow-sm"><i class="lab la-google"></i></a></li>
                                    @endif
                                    @if (get_setting('apple_login') == 1)
                                        <li class="list-inline-item"><a href="{{ route('social.login', ['provider' => 'apple']) }}"
                                                class="apple shadow-sm"><i class="lab la-apple"></i></a></li>
                                    @endif
                                </ul>
                            @endif

                            <!-- Register Link -->
                            <div class="text-center">
                                <p class="fs-14 text-muted mb-0">
                                    {{ translate('Don\'t have an account?') }}
                                    <a href="{{ route('user.registration') }}"
                                        class="fw-700 text-primary">{{ translate('Register Now') }}</a>
                                </p>
                            </div>

                            <!-- Back to Home -->
                            <div class="text-center mt-3">
                                <a href="{{ url('/') }}" class="fs-12 text-muted"><i class="las la-arrow-left"></i>
                                    {{ translate('Back to Homepage') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('script')
    <script>
        // Firebase Config
        const firebaseConfig = {
            apiKey: "{{ env('FIREBASE_WEB_API_KEY') }}",
            authDomain: "{{ env('FIREBASE_WEB_AUTH_DOMAIN') }}",
            projectId: "{{ env('FIREBASE_WEB_PROJECT_ID') }}",
            storageBucket: "{{ env('FIREBASE_WEB_PROJECT_ID') }}.appspot.com",
            messagingSenderId: "{{ env('FIREBASE_WEB_MESSAGING_SENDER_ID') }}",
            appId: "{{ env('FIREBASE_WEB_APP_ID') }}"
        };

        console.log('Firebase Config:', firebaseConfig); // Debug

        // Initialize Firebase
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        const auth = firebase.auth();
        auth.languageCode = '{{ app()->getLocale() }}';

        // State variables
        let confirmationResult = null;
        let isOtpMode = false;

        // Initialize Recaptcha
        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
            'size': 'invisible',
            'callback': (response) => {
                // reCAPTCHA solved, allow signInWithPhoneNumber.
                sendOTP();
            }
        });

        function switchLoginMode(mode) {
            isOtpMode = (mode === 'otp');

            // Tab styling
            if (isOtpMode) {
                $('#tab-password').removeClass('border-primary border-width-2 text-primary').addClass('text-muted');
                $('#tab-otp').addClass('border-primary border-width-2 text-primary').removeClass('text-muted');

                // Hide Password Logic but KEEP toggle
                $('.password-login-block').addClass('d-none');

                // Show currently selected input (Phone or Email)
                // We default to Phone if switching to OTP for the first time? 
                // Let's just respect the current toggle state or default to phone.
                if ($('#btn-use-email').hasClass('active')) {
                    $('.email-form-group').removeClass('d-none');
                    $('.phone-form-group').addClass('d-none');
                } else {
                    $('.phone-form-group').removeClass('d-none');
                    $('.email-form-group').addClass('d-none');
                    $('#btn-use-phone').addClass('active'); // Default
                }

                $('#btn-use-email').removeClass('d-none'); // Allow email in OTP mode now
                $('#btn-use-phone').removeClass('d-none');

                // Update Button Text
                $('.submit-button').text("{{ translate('Get OTP') }}");
                $('.submit-button').attr('type', 'button');
                $('.submit-button').attr('onclick', 'handleOtpFlow()');
            } else {
                $('#tab-otp').removeClass('border-primary border-width-2 text-primary').addClass('text-muted');
                $('#tab-password').addClass('border-primary border-width-2 text-primary').removeClass('text-muted');

                // Show Password Logic
                $('.password-login-block').removeClass('d-none');
                $('.otp-form-group').addClass('d-none');

                // Reset Toggle logic
                $('#btn-use-email').removeClass('d-none');

                if ($('#btn-use-email').hasClass('active')) {
                    $('.email-form-group').removeClass('d-none');
                    $('.phone-form-group').addClass('d-none');
                } else {
                    $('.phone-form-group').removeClass('d-none');
                    $('.email-form-group').addClass('d-none');
                }

                // Reset Button
                $('.submit-button').text("{{ translate('Login') }}");
                $('.submit-button').attr('type', 'submit');
                $('.submit-button').removeAttr('onclick');
            }
        }

        function toggleEmailPhone(type) {
            // REMOVED: if (isOtpMode) return;  <-- We allow toggle now

            // Just simple UI switching
            if (type === 'phone') {
                $('.phone-form-group').removeClass('d-none');
                $('.email-form-group').addClass('d-none');
                $('#btn-use-phone').addClass('active');
                $('#btn-use-email').removeClass('active');
            } else {
                $('.phone-form-group').addClass('d-none');
                $('.email-form-group').removeClass('d-none');
                $('#btn-use-phone').removeClass('active');
                $('#btn-use-email').addClass('active');
            }
        }

        function handleOtpFlow() {
            // Check if we already have the OTP input visible (e.g. sent)
            // But for Email, 'confirmationResult' is null.
            // We need a flag or check if OTP field is visible.
            if ($('.otp-form-group').hasClass('d-none')) {
                sendOTP();
            } else {
                verifyOTP();
            }
        }

        function sendOTP() {
            const isEmail = $('#btn-use-email').hasClass('active');

            if (isEmail) {
                // --- EMAIL OTP FLOW ---
                const email = $('#email').val();
                if (!email) {
                    AIZ.plugins.notify('warning', "{{ translate('Please enter your email') }}");
                    return;
                }

                $('.submit-button').prop('disabled', true).text("{{ translate('Sending...') }}");

                $.ajax({
                    type: "POST",
                    url: "{{ route('auth.login.otp.send') }}",
                    data: {
                        phone: email, // Controller expects 'phone' param even for email
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        $('.submit-button').prop('disabled', false).text("{{ translate('Verify & Login') }}");
                        if (response.result) {
                            $('.otp-form-group').removeClass('d-none');
                            $('.email-form-group').addClass('d-none'); // Hide input
                            AIZ.plugins.notify('success', response.message);
                        } else {
                            $('.submit-button').text("{{ translate('Get OTP') }}");
                            AIZ.plugins.notify('warning', response.message);
                        }
                    },
                    error: function (err) {
                        $('.submit-button').prop('disabled', false).text("{{ translate('Get OTP') }}");
                        AIZ.plugins.notify('danger', "{{ translate('Something went wrong') }}");
                        console.error(err);
                    }
                });

            } else {
                // --- PHONE OTP FLOW (Firebase) ---
                const phoneNumber = "+{{ $country_code ?? '91' }}" + $('#phone-code').val();
                const fullPhoneNumber = "+" + ($('input[name=country_code]').val() || '91') + $('#phone-code').val();

                $('.submit-button').prop('disabled', true).text("{{ translate('Sending...') }}");

                auth.signInWithPhoneNumber(fullPhoneNumber, window.recaptchaVerifier)
                    .then((result) => {
                        confirmationResult = result;
                        $('.otp-form-group').removeClass('d-none');
                        $('.phone-form-group').addClass('d-none');
                        $('.submit-button').text("{{ translate('Verify & Login') }}");
                        $('.submit-button').prop('disabled', false);
                        AIZ.plugins.notify('success', "{{ translate('OTP Sent Successfully') }}");
                    }).catch((error) => {
                        console.error("Error during signInWithPhoneNumber", error);
                        $('.submit-button').prop('disabled', false).text("{{ translate('Get OTP') }}");
                        window.recaptchaVerifier.render().then(function (widgetId) {
                            grecaptcha.reset(widgetId);
                        });
                        AIZ.plugins.notify('danger', error.message);
                    });
            }
        }

        function verifyOTP() {
            const code = $('#otp_code').val();
            if (!code) {
                AIZ.plugins.notify('warning', "{{ translate('Please enter the OTP') }}");
                return;
            }
            $('.submit-button').prop('disabled', true).text("{{ translate('Verifying...') }}");

            const isEmail = $('#btn-use-email').hasClass('active');

            if (isEmail) {
                // --- EMAIL VERIFY FLOW ---
                const email = $('#email').val();
                $.ajax({
                    type: "POST",
                    url: "{{ route('auth.login.otp.verify') }}",
                    data: {
                        phone: email,
                        otp_code: code,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.result) {
                            AIZ.plugins.notify('success', response.message);
                            window.location.href = response.redirect;
                        } else {
                            $('.submit-button').prop('disabled', false).text("{{ translate('Verify & Login') }}");
                            AIZ.plugins.notify('danger', response.message);
                        }
                    },
                    error: function (err) {
                        $('.submit-button').prop('disabled', false).text("{{ translate('Verify & Login') }}");
                        AIZ.plugins.notify('danger', "{{ translate('Verification failed') }}");
                    }
                });

            } else {
                // --- PHONE VERIFY FLOW (Firebase) ---
                confirmationResult.confirm(code).then((result) => {
                    const user = result.user;
                    user.getIdToken().then((idToken) => {
                        $('#firebase_id_token').val(idToken);
                        $('#firebase_verified_phone').val(user.phoneNumber);

                        const phoneNumber = user.phoneNumber;
                        const phoneWithoutPlus = phoneNumber.replace('+', '');
                        const last10 = phoneWithoutPlus.slice(-10);
                        $('#phone-code').val(last10);

                        document.getElementById('user-login-form').submit();
                    });
                }).catch((error) => {
                    console.error("Error during confirm", error);
                    $('.submit-button').prop('disabled', false).text("{{ translate('Verify & Login') }}");
                    AIZ.plugins.notify('danger', "{{ translate('Invalid OTP') }}");
                });
            }
        }

        // Auto-fill for demo (optional, keeping existing logic)
        function autoFillCustomer() {
            $('#email').val('customer@example.com');
            $('#password').val('123456');
        }


        $(document).ready(function () {
            // Default setup
            switchLoginMode('password');

            // If country code script isn't loaded, maybe hardcode '91' logic or rely on user
            $('input[name=country_code]').val('91'); // Default fallback
        });
    </script>

    @if(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_customer_login') == 1)
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('CAPTCHA_KEY') }}"></script>
        <script type="text/javascript">
            document.getElementById('user-login-form').addEventListener('submit', function (e) {
                // If this is a Firebase Login (Phone OTP), skip this generic reCAPTCHA handler
                // The OTP verification flow manually requires calling submit() which usually bypasses this,
                // but if we are here via a button click or other means:
                if (document.getElementById('firebase_verified_phone') && document.getElementById('firebase_verified_phone').value !== '') {
                    return;
                }

                // If in OTP mode but not verified yet, prevent default (this shouldn't happen via submit button as it is type=button)
                if (isOtpMode && $('#firebase_verified_phone').val() === '') {
                    e.preventDefault();
                    return;
                }

                if (!this.checkValidity()) {
                    return;
                }

                e.preventDefault();
                grecaptcha.ready(function () {
                    grecaptcha.execute(`{{ env('CAPTCHA_KEY') }}`, { action: 'login' }).then(function (token) {
                        var input = document.createElement('input');
                        input.setAttribute('type', 'hidden');
                        input.setAttribute('name', 'g-recaptcha-response');
                        input.setAttribute('value', token);
                        e.target.appendChild(input);

                        var actionInput = document.createElement('input');
                        actionInput.setAttribute('type', 'hidden');
                        actionInput.setAttribute('name', 'recaptcha_action');
                        actionInput.setAttribute('value', 'recaptcha_customer_login');
                        e.target.appendChild(actionInput);

                        e.target.submit();
                    });
                });
            });
        </script>
    @endif
@endsection