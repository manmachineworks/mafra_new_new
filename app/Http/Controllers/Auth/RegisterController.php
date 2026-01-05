<?php

namespace App\Http\Controllers\Auth;

use Cookie;
use Session;
use App\Models\Cart;
use App\Models\User;
use App\Rules\Recaptcha;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Controllers\OTPVerificationController;
use App\Utility\EmailUtility;
use App\Services\FirebaseTokenVerifier;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'g-recaptcha-response' => [
                Rule::when(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_customer_register') == 1 , ['required', new Recaptcha()], ['sometimes'])
            ]
        ];

        if ($this->firebaseOtpRegistrationRequired()) {
            $rules['phone'] = ['required', 'string'];
            $rules['firebase_id_token'] = ['required', 'string'];
            $rules['firebase_verified_phone'] = ['required', 'string'];
        } else {
            $rules['phone'] = ['sometimes', 'string'];
        }

        $validator = Validator::make($data, $rules);
        $validator->after(function ($validator) use ($data) {
            if (!empty($data['phone'])) {
                $normalized = $this->normalizePhone($data['phone'] ?? null, $data['country_code'] ?? null);
                if (!$normalized) {
                    $validator->errors()->add('phone', translate('Please enter a valid phone number in E.164 format (e.g. +15551234567).'));
                }
            }
        });

        return $validator;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // dd($data);
        $verifiedPhone = $data['firebase_verified_phone'] ?? session('firebase_verified_phone');
        $firebaseUid = $data['firebase_uid'] ?? session('firebase_uid');
        $isFirebaseVerified = !empty($data['firebase_id_token']);
        $normalizedPhone = $verifiedPhone ?: ($data['normalized_phone'] ?? $this->normalizePhone($data['phone'] ?? null, $data['country_code'] ?? null));

        if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'firebase_uid' => $firebaseUid,
                'phone_verified_at' => $isFirebaseVerified ? now() : null,
            ]);
        }
        else {
            if (addon_is_activated('otp_system')){
                $cleanPhone = preg_replace('/\D+/', '', $data['phone']);
                $user = User::create([
                    'name' => $data['name'],
                    'phone' => $normalizedPhone ?: '+'.$data['country_code'].$cleanPhone,
                    'password' => Hash::make($data['password']),
                    'verification_code' => rand(100000, 999999),
                    'firebase_uid' => $firebaseUid,
                    'phone_verified_at' => $isFirebaseVerified ? now() : null,
                ]);

                if(get_setting('customer_registration_verify') != '1' ){
                    $otpController = new OTPVerificationController;
                    $otpController->send_code($user);
                }

            }
        }
        
        if(session('temp_user_id') != null){
            if(auth()->user()->user_type == 'customer'){
                Cart::where('temp_user_id', session('temp_user_id'))
                ->update(
                    [
                        'user_id' => auth()->user()->id,
                        'temp_user_id' => null
                    ]
                );
            }
            else {
                Cart::where('temp_user_id', session('temp_user_id'))->delete();
            }
            Session::forget('temp_user_id');
        }

        if(Cookie::has('referral_code')){
            $referral_code = Cookie::get('referral_code');
            $referred_by_user = User::where('referral_code', $referral_code)->first();
            if($referred_by_user != null){
                $user->referred_by = $referred_by_user->id;
                $user->save();
            }
        }

        return $user;
    }

    public function register(Request $request)
    {
        $this->syncFirebaseVerification($request);
        $normalizedPhone = $this->normalizePhone($request->input('phone'), $request->input('country_code'));
        if ($normalizedPhone) {
            $request->merge(['normalized_phone' => $normalizedPhone]);
        }

        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            if(User::where('email', $request->email)->first() != null){
                flash(translate('Email or Phone already exists.'));
                if (get_setting('customer_registration_verify') == 1){
                    return route('registration.verification');
                }
                return back();
                
            }
        }
        elseif ($normalizedPhone && User::where('phone', $normalizedPhone)->first() != null) {
            flash(translate('Phone already exists.'));
            if (get_setting('customer_registration_verify') == 1){
                return route('registration.verification');
            }
            return back();
        }

        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        $this->guard()->login($user);

        if($user->email != null){
            if(BusinessSetting::where('type', 'email_verification')->first()->value != 1 || get_setting('customer_registration_verify') === '1'){
                $user->email_verified_at = date('Y-m-d H:m:s');
                $user->save();
                offerUserWelcomeCoupon();
                flash(translate('Registration successful.'))->success();
            }
            else {
                try {
                    EmailUtility::email_verification($user, 'customer');
                    flash(translate('Registration successful. Please verify your email.'))->success();
                } catch (\Throwable $e) {
                    dd($e);
                    $user->delete();
                    flash(translate('Registration failed. Please try again later.'))->error();
                }
            }

            // Account Opening Email to customer
            if ( $user != null && (get_email_template_data('registration_email_to_customer', 'status') == 1)) {
                try {
                    EmailUtility::customer_registration_email('registration_email_to_customer', $user, null);
                } catch (\Exception $e) {}
            }
        }

        if($user->phone != null){
            if(get_setting('email_verification') != 1 || get_setting('customer_registration_verify') === '1'){
                $user->email_verified_at = date('Y-m-d H:m:s');
                $user->save();
                offerUserWelcomeCoupon();
                flash(translate('Registration successful.'))->success();
            }
        }

        // customer Account Opening Email to Admin
        if ( $user != null && (get_email_template_data('customer_reg_email_to_admin', 'status') == 1)) {
            try {
                EmailUtility::customer_registration_email('customer_reg_email_to_admin', $user, null);
            } catch (\Exception $e) {}
        }

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    protected function registered(Request $request, $user)
    {
        if ($user->email == null && $user->email_verified_at == null) {
            return redirect()->route('verification');
        }elseif(session('link') != null){
            return redirect(session('link'));
        }else {
            return redirect()->route('home');
        }
    }

    private function syncFirebaseVerification(Request $request): void
    {
        if (!$this->firebaseOtpEnabled()) {
            return;
        }

        $token = $request->input('firebase_id_token');
        if (!$token && $this->firebaseOtpRegistrationRequired()) {
            abort(422, translate('Phone verification via Firebase is required.'));
        }

        if ($token) {
            try {
                $verified = app(FirebaseTokenVerifier::class)->verify($token);
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'phone' => translate('Phone verification via Firebase failed. Please try again.'),
                ]);
            }
            $normalizedRequestPhone = $this->normalizePhone($request->input('phone'), $request->input('country_code'));
            if ($normalizedRequestPhone && $verified['phone'] !== $normalizedRequestPhone) {
                throw ValidationException::withMessages([
                    'phone' => translate('The verified phone number does not match the number you entered.'),
                ]);
            }
            $request->merge([
                'firebase_verified_phone' => $verified['phone'],
                'firebase_uid' => $verified['uid'],
                'normalized_phone' => $verified['phone'],
            ]);
            session([
                'firebase_verified_phone' => $verified['phone'],
                'firebase_uid' => $verified['uid'],
            ]);
        }
    }

    private function firebaseOtpEnabled(): bool
    {
        return (bool) (get_setting('firebase_otp_enabled') == 1 && env('FIREBASE_OTP_ENABLED', false));
    }

    private function firebaseOtpRegistrationRequired(): bool
    {
        return $this->firebaseOtpEnabled() && get_setting('firebase_otp_require_registration') == 1;
    }

    private function normalizePhone(?string $phone, ?string $countryCode): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        $country = preg_replace('/\D+/', '', (string) $countryCode);

        if (empty($digits)) {
            return null;
        }

        if (strpos((string) $phone, '+') === 0) {
            $normalized = '+' . $digits;
        } elseif (!empty($country)) {
            $normalized = '+' . $country . $digits;
        } else {
            $normalized = '+' . $digits;
        }

        return preg_match('/^\+[1-9]\d{6,14}$/', $normalized) ? $normalized : null;
    }
}
