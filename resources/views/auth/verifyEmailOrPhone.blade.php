<script type="text/javascript">
    const firebaseWebConfig = window.firebaseWebConfig || @json(config('firebase.web'));
    const firebaseOtpEnabled = window.authPhoneOtpEnabled === true && firebaseWebConfig && firebaseWebConfig.api_key;
    let firebaseRecaptchaVerifier = null;
    let firebaseRecaptchaId = null;
    let firebaseConfirmationResult = null;

    function getFirebaseAppConfig() {
        return {
            apiKey: firebaseWebConfig.api_key || firebaseWebConfig.apiKey || '',
            authDomain: firebaseWebConfig.auth_domain || firebaseWebConfig.authDomain || '',
            projectId: firebaseWebConfig.project_id || firebaseWebConfig.projectId || '',
            appId: firebaseWebConfig.app_id || firebaseWebConfig.appId || '',
            messagingSenderId: firebaseWebConfig.messaging_sender_id || firebaseWebConfig.messagingSenderId || '',
            measurementId: firebaseWebConfig.measurement_id || firebaseWebConfig.measurementId || ''
        };
    }

    function ensureFirebase() {
        if (typeof firebase === 'undefined' || !firebase.auth) {
            return false;
        }
        if (!firebase.apps || !firebase.apps.length) {
            firebase.initializeApp(getFirebaseAppConfig());
        }
        return true;
    }

    function ensureFirebaseRecaptcha() {
        if (firebaseRecaptchaVerifier) {
            return;
        }
        let container = document.getElementById('firebase-reg-recaptcha');
        if (!container) {
            container = document.createElement('div');
            container.id = 'firebase-reg-recaptcha';
            container.className = 'd-none';
            document.body.appendChild(container);
        }
        firebaseRecaptchaVerifier = new firebase.auth.RecaptchaVerifier(container, { size: 'invisible' });
        firebaseRecaptchaVerifier.render().then(function (id) {
            firebaseRecaptchaId = id;
        });
    }

    function resetFirebaseRecaptcha() {
        if (typeof grecaptcha !== 'undefined' && firebaseRecaptchaId !== null) {
            grecaptcha.reset(firebaseRecaptchaId);
        }
    }

    function normalizePhone(phone, countryCode) {
        let value = (phone || '').trim();
        value = value.replace(/[^0-9+]/g, '');
        if (value.indexOf('+') !== 0) {
            value = `+${countryCode}${value}`;
        }
        return value;
    }

    function setFirebaseToken(token) {
        let input = document.getElementById('firebase_id_token');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'firebase_id_token';
            input.id = 'firebase_id_token';
            const form = document.getElementById('reg-form');
            if (form) {
                form.appendChild(input);
            }
        }
        input.value = token;
    }

    function sendVerificationCode(clickedBtn = null) {
        let recaptchaEnabled = {{ get_setting('google_recaptcha') == 1 ? 'true' : 'false' }};

        let btn = clickedBtn ? clickedBtn : document.getElementById('sendOtpBtn');
        let email = $('#signinSrEmail').length ? $('#signinSrEmail').val() : $('#signinAddonEmail').val();
        let phone = $('#phone-code').length ? $('#phone-code').val() : '';
        let country_code = $('input[name="country_code"]').val() ?? '';

        let identifier = email ? email : phone;
        if (!identifier) {
            AIZ.plugins.notify('danger', '{{ translate("Please enter your email or phone number") }}');
            return;
        }

        let emailPhoneDiv = $('#emailOrPhoneDiv');
        let codeGroup = $('#verification_code').closest('.form-group');
        let originalText = $(btn).html();

        $(btn).prop('disabled', true).text('Sending...');

        if (!email && phone && firebaseOtpEnabled) {
            if (!ensureFirebase()) {
                AIZ.plugins.notify('danger', '{{ translate("Firebase is not available on this page.") }}');
                $(btn).prop('disabled', false).html(originalText);
                return;
            }
            const phoneE164 = normalizePhone(phone, country_code);
            ensureFirebaseRecaptcha();
            firebase.auth().signInWithPhoneNumber(phoneE164, firebaseRecaptchaVerifier)
                .then(function (result) {
                    firebaseConfirmationResult = result;
                    emailPhoneDiv.addClass('d-none');
                    codeGroup.removeClass('d-none').addClass('d-block');
                    AIZ.plugins.notify('success', '{{ translate("OTP sent successfully.") }}');
                })
                .catch(function (error) {
                    resetFirebaseRecaptcha();
                    AIZ.plugins.notify('danger', error && error.message ? error.message : '{{ translate("Failed to send OTP.") }}');
                })
                .finally(function () {
                    $(btn).prop('disabled', false).html(originalText);
                });
            return;
        }

        function ajaxSend(data) {
            $.post('{{ route("customer-reg.verification_code_send") }}', data, function (res) {

                if (res.status == 1) {
                    AIZ.plugins.notify('success', res.message);
                    emailPhoneDiv.addClass('d-none');
                    codeGroup.removeClass('d-none').addClass('d-block');

                } else if (res.status == 2) {
                    AIZ.plugins.notify('danger', res.message);

                } else {
                    AIZ.plugins.notify('danger', res.message);
                }

            }).fail(function () {
                AIZ.plugins.notify('danger', 'Something went wrong');
            }).always(function () {
                $(btn).prop('disabled', false).html(originalText);
            });
        }

        let postData = {
            _token: '{{ csrf_token() }}',
            email: email,
            phone: phone,
            country_code: country_code
        };

        if (recaptchaEnabled) {
            grecaptcha.ready(function () {
                grecaptcha.execute(`{{ env('CAPTCHA_KEY') }}`, {
                    action: 'verification_code_send'
                }).then(function (token) {
                    postData['g-recaptcha-response'] = token;
                    ajaxSend(postData);
                });
            });
        } else {
            ajaxSend(postData);
        }
    }

    const codeInput = document.getElementById('verification_code');
    const verifyBtn = document.getElementById('verifyOtpBtn');

    //realtime validation
    codeInput.addEventListener('input', function() {

        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 6) this.value = this.value.slice(0,6);

        if (this.value.length === 6) {
            verifyBtn.innerHTML = '<i class="las la-lg la-spinner la-spin"></i>';

            let email = $('#signinSrEmail').length ? $('#signinSrEmail').val() : $('#signinAddonEmail').val();
            let phone = $('#phone-code').length ? $('#phone-code').val() : '';
            let country_code = $('input[name="country_code"]').val()?? '';

            if (!email && phone && firebaseOtpEnabled && firebaseConfirmationResult) {
                firebaseConfirmationResult.confirm(this.value)
                    .then(function (result) {
                        return result.user.getIdToken();
                    })
                    .then(function (token) {
                        setFirebaseToken(token);
                        verifyBtn.innerHTML = '<i class="las la-lg la-check-circle text-success"></i>';
                        AIZ.plugins.notify('success', '{{ translate("Verification successful.") }}');
                        codeInput.disabled = true;
                        verifyBtn.classList.add('disabled');
                        verifyBtn.style.backgroundColor = '#f7f8fa';
                        toggleCreateBtn();
                    })
                    .catch(function (error) {
                        resetFirebaseRecaptcha();
                        verifyBtn.innerHTML = '<i class="las la-lg la-times-circle text-danger"></i>';
                        AIZ.plugins.notify('danger', error && error.message ? error.message : '{{ translate("Invalid OTP code.") }}');
                        toggleCreateBtn();
                    });
                return;
            }

            $.post('{{ route("customer-reg.verify_code_confirmation") }}', {
                _token: '{{ csrf_token() }}',
                verification_code: this.value,
                email: email,
                phone: phone,
                country_code: country_code
            }, function(data) {
                if(data.status === 1){
                    verifyBtn.innerHTML = '<i class="las la-lg la-check-circle text-success"></i>';
                    AIZ.plugins.notify('success', `${data.message}`);
                    codeInput.disabled = true;
                    verifyBtn.classList.add('disabled');
                    verifyBtn.style.backgroundColor = '#f7f8fa';
                    toggleCreateBtn();

                } else {
                    AIZ.plugins.notify('danger', `${data.message}`);
                    verifyBtn.innerHTML = '<i class="las la-lg la-times-circle text-danger"></i>';
                    toggleCreateBtn();
                }
            });
        } else {
            verifyBtn.innerHTML = '<i class="las la-lg la-arrow-right"></i>';
        }
    });
</script>
