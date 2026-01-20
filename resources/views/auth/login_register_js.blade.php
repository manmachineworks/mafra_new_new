<script src="{{ static_asset('assets/js/vendors.js') }}"></script>
<script>
    (function ($) {
        "use strict";
        AIZ.data = {
            csrf: $('meta[name="csrf-token"]').attr("content"),
            appUrl: $('meta[name="app-url"]').attr("content"),
            fileBaseUrl: $('meta[name="file-base-url"]').attr("content"),
        };
        AIZ.plugins = {
            notify: function (type = "dark", message = "") {
                $.notify(
                    { message: message },
                    {
                        showProgressbar: true,
                        delay: 2500,
                        mouse_over: "pause",
                        placement: { from: "bottom", align: "left" },
                        animate: { enter: "animated fadeInUp", exit: "animated fadeOutDown" },
                        type: type,
                        template: '<div data-notify="container" class="aiz-notify alert alert-{0}" role="alert">' +
                            '<button type="button" aria-hidden="true" data-notify="dismiss" class="close"><i class="las la-times"></i></button>' +
                            '<span data-notify="message">{2}</span>' +
                            '<div class="progress" data-notify="progressbar">' +
                            '<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
                            "</div>" +
                            "</div>",
                    }
                );
            }
        };
    })(jQuery);
</script>

<script>
    @foreach (session('flash_notification', collect())->toArray() as $message)
        AIZ.plugins.notify('{{ $message['level'] }}', '{{ $message['message'] }}');
    @endforeach

    // Password Toggle
    $('.password-toggle').click(function () {
        var $this = $(this);
        if ($this.siblings('input').attr('type') == 'password') {
            $this.siblings('input').attr('type', 'text');
            $this.removeClass('la-eye').addClass('la-eye-slash');
        } else {
            $this.siblings('input').attr('type', 'password');
            $this.removeClass('la-eye-slash').addClass('la-eye');
        }
    });

    // Global State
    var isPhoneShown = true;
    var loginMode = 'password';

    // Initialize Phone Input
    var input = document.querySelector("#phone-code");
    var iti = intlTelInput(input, {
        separateDialCode: true,
        utilsScript: "{{ static_asset('assets/js/intlTelutils.js') }}?1590403638580",
        onlyCountries: @php echo get_active_countries()->pluck('code') @endphp,
        customPlaceholder: function (selectedCountryPlaceholder, selectedCountryData) {
            if (selectedCountryData.iso2 == 'bd') { return "01xxxxxxxxx"; }
            return selectedCountryPlaceholder;
        }
    });

    var country = iti.getSelectedCountryData();
    $('input[name=country_code]').val(country.dialCode);

    input.addEventListener("countrychange", function (e) {
        var country = iti.getSelectedCountryData();
        $('input[name=country_code]').val(country.dialCode);
    });

    // Functions
    function switchLoginMode(mode) {
        loginMode = mode;

        // Reset State
        $('#otp_code').val('');
        $('.submit-button').prop('disabled', false);

        if (mode === 'password') {
            // UI Update
            $('#tab-password').addClass('border-bottom border-primary border-width-2 text-primary').removeClass('text-muted');
            $('#tab-otp').removeClass('border-bottom border-primary border-width-2 text-primary').addClass('text-muted');

            $('.password-login-block').removeClass('d-none');
            $('.otp-form-group').addClass('d-none');

            // Button & Action Update
            $('.submit-button').html('{{ translate('Login') }}').attr('onclick', '').attr('type', 'submit');
            $('.loginForm').attr('action', '{{ route('login') }}');

        } else {
            // UI Update
            $('#tab-otp').addClass('border-bottom border-primary border-width-2 text-primary').removeClass('text-muted');
            $('#tab-password').removeClass('border-bottom border-primary border-width-2 text-primary').addClass('text-muted');

            $('.password-login-block').addClass('d-none');
            $('.otp-form-group').addClass('d-none'); // Wait for sending

            // Button & Action Update
            $('.submit-button').html('{{ translate('Get OTP') }}').attr('onclick', 'sendOtp()').attr('type', 'button');
        }
    }

    function toggleEmailPhone(type) {
        // Clear inputs on switch
        $('#phone-code').val('');
        $('#email').val('');

        if (type === 'phone') {
            isPhoneShown = true;
            $('#btn-use-phone').addClass('active btn-primary text-white').removeClass('btn-outline-primary');
            $('#btn-use-email').removeClass('active btn-primary text-white').addClass('btn-outline-primary');

            $('.phone-form-group').removeClass('d-none');
            $('.email-form-group').addClass('d-none');
        } else {
            isPhoneShown = false;
            $('#btn-use-email').addClass('active btn-primary text-white').removeClass('btn-outline-primary');
            $('#btn-use-phone').removeClass('active btn-primary text-white').addClass('btn-outline-primary');

            $('.email-form-group').removeClass('d-none');
            $('.phone-form-group').addClass('d-none');
        }
    }

    function sendOtp() {
        if (isPhoneShown) {
            // PHONE OTP (Firebase)
            var phone = $('#phone-code').val();
            var countryCode = $('input[name=country_code]').val();

            if (phone === "") {
                AIZ.plugins.notify('danger', '{{ translate('Please enter phone number') }}');
                return;
            }

            $('.submit-button').prop('disabled', true).html('{{ translate('Sending OTP...') }}');

            // Check Recaptcha
            if (!window.recaptchaVerifier) {
                // Using 'normal' size (visible) to avoid invisible reCAPTCHA issues on localhost
                try {
                    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                        'size': 'normal',
                        'callback': function (response) {
                            // reCAPTCHA solved
                            // we could enable the button here or auto-send
                        },
                        'expired-callback': function () {
                            // Response expired. Ask user to solve reCAPTCHA again.
                            AIZ.plugins.notify('warning', '{{ translate('reCAPTCHA expired. Please solve it again.') }}');
                            window.recaptchaVerifier.reset();
                        }
                    });

                    window.recaptchaVerifier.render().then(function (widgetId) {
                        window.recaptchaWidgetId = widgetId;
                    });
                } catch (e) {
                    console.error("Recaptcha Init Error: ", e);
                    // If it's already rendered, maybe we just reset it?
                    // In dev, sometimes hot-reload causes issues, but page refresh clears it.
                    if (window.recaptchaVerifier) {
                        window.recaptchaVerifier.clear();
                        $('#recaptcha-container').empty();
                    }
                }
            }

            // If the widget is visible but not solved, signInWithPhoneNumber might error or wait. 
            // Better to let the user solve it. 
            // Ideally we show the widget, wait for callback, THEN call signIn. 
            // But Firebase SDK allows calling it directly and it handles the flow.
            // Let's rely on standard SDK behavior.

            var phoneNumber = "+" + countryCode + phone;

            firebase.auth().signInWithPhoneNumber(phoneNumber, window.recaptchaVerifier)
                .then(function (confirmationResult) {
                    window.confirmationResult = confirmationResult;

                    AIZ.plugins.notify('success', '{{ translate('OTP Sent!') }}');

                    $('.otp-form-group').removeClass('d-none');
                    $('.submit-button').html('{{ translate('Login') }}').attr('onclick', 'verifyOtp()').prop('disabled', false);

                }).catch(function (error) {
                    $('.submit-button').prop('disabled', false).html('{{ translate('Get OTP') }}');

                    var errorMessage = error.message;
                    if (error.code === 'auth/invalid-app-credential') {
                        errorMessage = '{{ translate('Domain not authorized or reCAPTCHA configuration error. Check Firebase Console > Authorized Domains and ensure localhost is added.') }}';
                    } else if (error.code === 'auth/too-many-requests') {
                        errorMessage = '{{ translate('Too many requests. Please try again later.') }}';
                    } else if (error.code === 'auth/captcha-check-failed') {
                        errorMessage = '{{ translate('reCAPTCHA check failed. Please try again.') }}';
                    }

                    AIZ.plugins.notify('danger', errorMessage);
                    console.error("Firebase Error:", error);

                    // Reset reCAPTCHA on error so user can try again
                    if (window.recaptchaVerifier) {
                        try {
                            window.recaptchaVerifier.render().then(function (widgetId) {
                                grecaptcha.reset(widgetId);
                            });
                        } catch (e) {
                            // verify might not be rendered yet or already cleared
                            // window.recaptchaVerifier.clear(); // This removes it from DOM
                            // Better to just empty the container and nullify if we want a fresh start
                            window.recaptchaVerifier.clear();
                            window.recaptchaVerifier = null;
                            $('#recaptcha-container').empty();
                        }
                    }
                });

        } else {
            // EMAIL OTP (Backend)
            var email = $('#email').val();
            if (email === "") {
                AIZ.plugins.notify('danger', '{{ translate('Please enter email address') }}');
                return;
            }

            $('.submit-button').prop('disabled', true).html('{{ translate('Sending OTP...') }}');

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('send-otp') }}',
                type: 'POST',
                data: {
                    phone: email // We send email in 'phone' field as per controller logic
                },
                success: function (data) {
                    if (data.result) {
                        AIZ.plugins.notify('success', data.message);
                        $('.otp-form-group').removeClass('d-none');
                        $('.submit-button').html('{{ translate('Login') }}').attr('onclick', 'verifyOtp()').prop('disabled', false);
                    } else {
                        AIZ.plugins.notify('danger', data.message);
                        $('.submit-button').prop('disabled', false).html('{{ translate('Get OTP') }}');
                    }
                },
                error: function () {
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                    $('.submit-button').prop('disabled', false).html('{{ translate('Get OTP') }}');
                }
            });
        }
    }

    function verifyOtp() {
        var code = $('#otp_code').val();
        if (code === "") {
            AIZ.plugins.notify('danger', '{{ translate('Please enter OTP') }}');
            return;
        }

        $('.submit-button').prop('disabled', true).html('{{ translate('Verifying...') }}');

        if (isPhoneShown) {
            // PHONE VERIFY (Firebase)
            window.confirmationResult.confirm(code).then(function (result) {
                var user = result.user;
                user.getIdToken().then(function (idToken) {
                    $('#firebase_id_token').val(idToken);
                    $('#firebase_verified_phone').val(user.phoneNumber);

                    $('.submit-button').prop('type', 'submit').removeAttr('onclick');
                    $('.loginForm').submit();
                });

            }).catch(function (error) {
                $('.submit-button').prop('disabled', false).html('{{ translate('Login') }}');
                AIZ.plugins.notify('danger', '{{ translate('Invalid OTP') }}');
            });
        } else {
            // EMAIL VERIFY (Backend)
            var email = $('#email').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('validate-otp-code') }}',
                type: 'POST',
                data: {
                    phone: email,
                    otp_code: code
                },
                success: function (data) {
                    if (data.result) {
                        AIZ.plugins.notify('success', data.message);
                        window.location.href = data.redirect;
                    } else {
                        AIZ.plugins.notify('danger', data.message);
                        $('.submit-button').prop('disabled', false).html('{{ translate('Login') }}');
                    }
                },
                error: function () {
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                    $('.submit-button').prop('disabled', false).html('{{ translate('Login') }}');
                }
            });
        }
    }
</script>

<script>
    window.authPhoneOtpEnabled = {{ get_setting('auth_phone_otp_enabled', 1) == 1 ? 'true' : 'false' }};
    window.firebaseWebConfig = @json(config('firebase.web'));
</script>