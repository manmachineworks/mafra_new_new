<script src="{{ static_asset('assets/js/vendors.js') }}"></script>
<script>
    (function ($) {
        // USE STRICT
        "use strict";

        AIZ.data = {
            csrf: $('meta[name="csrf-token"]').attr("content"),
            appUrl: $('meta[name="app-url"]').attr("content"),
            fileBaseUrl: $('meta[name="file-base-url"]').attr("content"),
        };
        AIZ.plugins = {
            notify: function (type = "dark", message = "") {
                $.notify(
                    {
                        // options
                        message: message,
                    },
                    {
                        // settings
                        showProgressbar: true,
                        delay: 2500,
                        mouse_over: "pause",
                        placement: {
                            from: "bottom",
                            align: "left",
                        },
                        animate: {
                            enter: "animated fadeInUp",
                            exit: "animated fadeOutDown",
                        },
                        type: type,
                        template:
                            '<div data-notify="container" class="aiz-notify alert alert-{0}" role="alert">' +
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

    $('.password-toggle').click(function(){
        var $this = $(this);
        if ($this.siblings('input').attr('type') == 'password') {
            $this.siblings('input').attr('type', 'text');
            $this.removeClass('la-eye').addClass('la-eye-slash');
        } else {
            $this.siblings('input').attr('type', 'password');
            $this.removeClass('la-eye-slash').addClass('la-eye');
        }
    });
</script>

@if (addon_is_activated('otp_system'))
    <script type="text/javascript">
        // Country Code
        var isPhoneShown = true,
            countryData = window.intlTelInputGlobals.getCountryData(),
            input = document.querySelector("#phone-code");

        for (var i = 0; i < countryData.length; i++) {
            var country = countryData[i];
            if (country.iso2 == 'bd') {
                country.dialCode = '88';
            }
        }

        var iti = intlTelInput(input, {
            separateDialCode: true,
            utilsScript: "{{ static_asset('assets/js/intlTelutils.js') }}?1590403638580",
            onlyCountries: @php echo get_active_countries()->pluck('code') @endphp,
            customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
                if (selectedCountryData.iso2 == 'bd') {
                    return "01xxxxxxxxx";
                }
                return selectedCountryPlaceholder;
            }
        });

        var country = iti.getSelectedCountryData();
        $('input[name=country_code]').val(country.dialCode);

        input.addEventListener("countrychange", function(e) {
            // var currentMask = e.currentTarget.placeholder;
            var country = iti.getSelectedCountryData();
            $('input[name=country_code]').val(country.dialCode);

        });

        function toggleEmailPhone(el) {
            if (isPhoneShown) {
                $('.phone-form-group').addClass('d-none');
                $('.email-form-group').removeClass('d-none');
                $('input[name=phone]').val(null);
                isPhoneShown = false;
                $(el).html('*{{ translate('Use Phone Number Instead') }}');

                $('.toggle-login-with-otp').addClass('d-none');

            } else {
                $('.phone-form-group').removeClass('d-none');
                $('.email-form-group').addClass('d-none');
                $('input[name=email]').val(null);
                isPhoneShown = true;
                $(el).html('<i>*{{ translate('Use Email Instead') }}</i>');

                $('.toggle-login-with-otp').removeClass('d-none');
            }
            
            $('.submit-button').html('{{ translate('Login') }}');
            $('.password-login-block').removeClass('d-none');
            
            var url = '{{ route('login') }}';
            $('.loginForm').attr('action', url);
        }

        function toggleLoginPassOTP() {
            $('.password-login-block').addClass('d-none');
            $('.submit-button').html('{{ translate('Login With OTP') }}');

            var url = '{{ route('send-otp') }}';
            $('.loginForm').attr('action', url);
        }
    </script> 
@endif

@php
    $firebaseOtpEnabled = get_setting('firebase_otp_enabled') == 1 && env('FIREBASE_OTP_ENABLED', false);
@endphp
@if ($firebaseOtpEnabled)
    <div id="firebase-recaptcha-container" class="d-none"></div>
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js";
        import { getAuth, RecaptchaVerifier, signInWithPhoneNumber } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-auth.js";

        const firebaseConfig = {
            apiKey: "{{ env('FIREBASE_API_KEY') }}",
            authDomain: "{{ env('FIREBASE_AUTH_DOMAIN') }}",
            projectId: "{{ env('FIREBASE_PROJECT_ID') }}",
            storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET') }}",
            messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID') }}",
            appId: "{{ env('FIREBASE_APP_ID') }}",
            measurementId: "{{ env('FIREBASE_MEASUREMENT_ID') }}",
        };

        const configMissing = !firebaseConfig.apiKey || !firebaseConfig.authDomain || !firebaseConfig.projectId;
        if (configMissing) {
            console.warn('Firebase OTP config is incomplete.');
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.js-send-firebase-otp, .js-verify-firebase-otp').forEach((btn) => {
                    btn.setAttribute('disabled', 'disabled');
                });
            });
        }

        if (!configMissing) {
            const firebaseApp = initializeApp(firebaseConfig);
            const firebaseAuth = getAuth(firebaseApp);
            firebaseAuth.useDeviceLanguage();

            const firebaseOtpState = {
                confirmation: null,
                lastSentAt: 0,
                cooldown: 60,
                isVerifying: false,
            };

            const E164_PATTERN = /^\+[1-9]\d{6,14}$/;

            const notifyError = (message) => AIZ.plugins.notify('danger', message);
            const notifySuccess = (message) => AIZ.plugins.notify('success', message);
            const emitOtpEvent = (name, detail = {}) => document.dispatchEvent(new CustomEvent(name, { detail }));

            function recaptcha() {
                if (window.firebaseRecaptcha) return window.firebaseRecaptcha;
                window.firebaseRecaptcha = new RecaptchaVerifier(firebaseAuth, 'firebase-recaptcha-container', {
                    size: 'invisible',
                    callback: () => {},
                    'expired-callback': () => {
                        emitOtpEvent('firebase-otp-reset', {});
                    },
                });
                return window.firebaseRecaptcha;
            }

            function resetRecaptcha() {
                if (window.firebaseRecaptcha && typeof window.firebaseRecaptcha.clear === 'function') {
                    window.firebaseRecaptcha.clear();
                    window.firebaseRecaptcha = null;
                }
            }

            function normalizePhone(raw, countryCode = '') {
                const digits = (raw || '').replace(/\D/g, '');
                const cc = (countryCode || '').replace(/\D/g, '');
                if (!digits) return '';
                if ((raw || '').trim().startsWith('+')) {
                    return `+${digits}`;
                }
                if (cc) {
                    return `+${cc}${digits}`;
                }
                return `+${digits}`;
            }

            function mapFirebaseError(error) {
                const code = (error && error.code) ? error.code : '';
                const messages = {
                    'auth/invalid-phone-number': '{{ translate('Please enter a valid phone number in E.164 format (e.g. +15551234567).') }}',
                    'auth/missing-phone-number': '{{ translate('Phone number is required to send OTP.') }}',
                    'auth/too-many-requests': '{{ translate('Too many OTP attempts. Please wait and try again.') }}',
                    'auth/captcha-check-failed': '{{ translate('reCAPTCHA check failed. Please retry.') }}',
                    'auth/invalid-verification-code': '{{ translate('Invalid verification code. Please try again.') }}',
                    'auth/code-expired': '{{ translate('The verification code has expired. Please request a new OTP.') }}',
                };
                return messages[code] || '{{ translate('Unable to verify phone number. Please try again.') }}';
            }

            async function sendFirebaseOtp(phone, triggerBtn) {
                const now = Date.now();
                if (now - firebaseOtpState.lastSentAt < firebaseOtpState.cooldown * 1000) {
                    AIZ.plugins.notify('info', '{{ translate('Please wait before requesting another OTP.') }}');
                    return;
                }
                if (!E164_PATTERN.test(phone)) {
                    notifyError('{{ translate('Please enter a valid phone number in E.164 format (e.g. +15551234567).') }}');
                    return;
                }
                try {
                    if (triggerBtn) {
                        triggerBtn.setAttribute('disabled', 'disabled');
                        triggerBtn.classList.add('disabled');
                        triggerBtn.setAttribute('data-original-text', triggerBtn.innerHTML || '');
                        triggerBtn.innerHTML = '{{ translate('Sending...') }}';
                    }
                    resetRecaptcha();
                    firebaseOtpState.confirmation = await signInWithPhoneNumber(firebaseAuth, phone, recaptcha());
                    firebaseOtpState.lastSentAt = now;
                    notifySuccess('{{ translate('OTP sent successfully.') }}');
                    emitOtpEvent('firebase-otp-sent', { phone });
                } catch (error) {
                    console.error(error);
                    notifyError(mapFirebaseError(error));
                    emitOtpEvent('firebase-otp-reset', { reason: 'send_failed' });
                } finally {
                    if (triggerBtn) {
                        const original = triggerBtn.getAttribute('data-original-text');
                        triggerBtn.innerHTML = original || triggerBtn.innerHTML;
                        triggerBtn.removeAttribute('disabled');
                        triggerBtn.classList.remove('disabled');
                    }
                }
            }

            async function verifyFirebaseOtp(code, formEl, otpInput) {
                if (!firebaseOtpState.confirmation) {
                    notifyError('{{ translate('Please request an OTP first.') }}');
                    emitOtpEvent('firebase-otp-reset', { reason: 'missing_confirmation' });
                    return;
                }
                if (!code || code.trim().length < 6) {
                    notifyError('{{ translate('Enter the 6-digit verification code sent to your phone.') }}');
                    return;
                }
                try {
                    firebaseOtpState.isVerifying = true;
                    if (otpInput) {
                        otpInput.setAttribute('disabled', 'disabled');
                    }
                    const result = await firebaseOtpState.confirmation.confirm(code.trim());
                    const idToken = await result.user.getIdToken(true);
                    const payload = new URLSearchParams({
                        id_token: idToken,
                        _token: AIZ.data.csrf,
                    });

                    const response = await fetch('{{ route('firebase.verify-phone') }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: payload.toString(),
                    });

                    if (!response.ok) {
                        const err = await response.json();
                        throw new Error(err.message || 'Verification failed');
                    }

                    const data = await response.json();
                    formEl.querySelectorAll('[name="firebase_id_token"]').forEach((input) => input.value = idToken);
                    formEl.querySelectorAll('[name="firebase_verified_phone"]').forEach((input) => input.value = data.phone);
                    formEl.querySelectorAll('[name="firebase_uid"]').forEach((input) => input.value = data.firebase_uid);

                    formEl.querySelectorAll('.js-requires-otp').forEach((btn) => btn.removeAttribute('disabled'));
                    notifySuccess('{{ translate('Phone verified. You can continue.') }}');
                    emitOtpEvent('firebase-otp-verified', {
                        formId: formEl.id || null,
                        phone: data.phone,
                        firebase_uid: data.firebase_uid,
                    });
                } catch (error) {
                    console.error(error);
                    notifyError(mapFirebaseError(error));
                    firebaseOtpState.confirmation = null;
                    emitOtpEvent('firebase-otp-reset', { reason: 'verify_failed' });
                } finally {
                    firebaseOtpState.isVerifying = false;
                    if (otpInput) {
                        otpInput.removeAttribute('disabled');
                    }
                    resetRecaptcha();
                }
            }

            document.addEventListener('click', function (e) {
                const sendBtn = e.target.closest('.js-send-firebase-otp');
                if (sendBtn) {
                    const formSelector = sendBtn.getAttribute('data-target-form');
                    const phoneSelector = sendBtn.getAttribute('data-phone-input');
                    const countrySelector = sendBtn.getAttribute('data-country-input');
                    const otpWrapperSelector = sendBtn.getAttribute('data-otp-wrapper');
                    const form = document.querySelector(formSelector);
                    const phoneInput = document.querySelector(phoneSelector);
                    const countryInput = countrySelector ? document.querySelector(countrySelector) : null;
                    if (!form || !phoneInput) return;

                    const raw = phoneInput.value || '';
                    const normalized = normalizePhone(raw, countryInput ? countryInput.value : '');
                    if (!raw.trim()) {
                        AIZ.plugins.notify('danger', '{{ translate('Enter a valid phone number before requesting OTP.') }}');
                        return;
                    }
                    if (!E164_PATTERN.test(normalized)) {
                        notifyError('{{ translate('Please enter a valid phone number in E.164 format (e.g. +15551234567).') }}');
                        return;
                    }
                    sendFirebaseOtp(normalized, sendBtn).then(() => {
                        if (otpWrapperSelector) {
                            const otpWrapper = document.querySelector(otpWrapperSelector);
                            if (otpWrapper) {
                                otpWrapper.classList.remove('d-none');
                            }
                            emitOtpEvent('firebase-otp-prompt', { formId: form ? form.id : null });
                        }
                    });
                }

                const verifyBtn = e.target.closest('.js-verify-firebase-otp');
                if (verifyBtn) {
                    const formSelector = verifyBtn.getAttribute('data-target-form');
                    const otpSelector = verifyBtn.getAttribute('data-otp-input');
                    const form = document.querySelector(formSelector);
                    const otpInput = document.querySelector(otpSelector);
                    if (!form || !otpInput) return;
                    verifyBtn.setAttribute('disabled', 'disabled');
                    verifyBtn.classList.add('disabled');
                    verifyFirebaseOtp(otpInput.value, form, otpInput);
                    setTimeout(() => {
                        verifyBtn.removeAttribute('disabled');
                        verifyBtn.classList.remove('disabled');
                    }, 400);
                }
            });
        }
    </script>
@endif
