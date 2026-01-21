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
                                style="background-image: url('{{ uploaded_asset(get_setting('password_reset_page_image')) }}'); min-height: 500px;">
                            </div>

                            <!-- Right Side Form -->
                            <div class="col-lg-6 p-4 p-md-5 d-flex flex-column justify-content-center border-left">
                                <!-- Site Icon -->
                                <div class="text-center mb-4">
                                    <img src="{{ uploaded_asset(get_setting('site_icon')) }}"
                                        alt="{{ translate('Site Icon')}}" style="height: 48px;">
                                </div>

                                <!-- Titles -->
                                <div class="text-center mb-4">
                                    <h1 class="h3 fw-700 text-primary mb-2">{{ translate('Reset Password') }}</h1>
                                    <p class="text-muted mb-0">
                                        {{ translate('Enter the code sent to your email and your new password.') }}
                                    </p>
                                </div>

                                <!-- Form -->
                                <form class="form-default" role="form" action="{{ route('password.update') }}"
                                    method="POST">
                                    @csrf

                                    <!-- Email -->
                                    <div class="form-group">
                                        <label class="form-label text-dark fw-600">{{ translate('Email Address') }}</label>
                                        <input id="email" type="email" class="form-control rounded-lg" name="email"
                                            value="{{ $email ?? old('email') }}" @if(!empty($email ?? null)) readonly @endif
                                            placeholder="{{ translate('johndoe@example.com') }}" required>
                                        @if ($errors->has('email'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('email') }}</span>
                                        @endif
                                    </div>

                                    <!-- Code -->
                                    <div class="form-group">
                                        <label
                                            class="form-label text-dark fw-600">{{ translate('Verification Code') }}</label>
                                        <input id="code" type="text" class="form-control rounded-lg" name="code"
                                            value="{{ old('code') }}" placeholder="{{ translate('Enter Code') }}" required
                                            autofocus>
                                        @if ($errors->has('code'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('code') }}</span>
                                        @endif
                                    </div>

                                    <!-- Password -->
                                    <div class="form-group">
                                        <label class="form-label text-dark fw-600">{{ translate('New Password') }}</label>
                                        <input id="password" type="password" class="form-control rounded-lg" name="password"
                                            placeholder="{{ translate('New Password') }}" required>
                                        @if ($errors->has('password'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('password') }}</span>
                                        @endif
                                    </div>

                                    <!-- Password Confirmation -->
                                    <div class="form-group mb-4">
                                        <label
                                            class="form-label text-dark fw-600">{{ translate('Confirm Password') }}</label>
                                        <input id="password-confirm" type="password" class="form-control rounded-lg"
                                            name="password_confirmation"
                                            placeholder="{{ translate('Confirm New Password') }}" required>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="mb-3">
                                        <button type="submit"
                                            class="btn btn-primary btn-block btn-lg fw-600 rounded-lg transition-3d-hover shadow-sm">
                                            {{ translate('Reset Password') }}
                                        </button>
                                    </div>

                                    <!-- Back Link -->
                                    <div class="text-center">
                                        <a href="{{ url()->previous() }}" class="text-reset fw-600 fs-13">
                                            <i class="las la-arrow-left mr-1"></i>{{ translate('Back') }}
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