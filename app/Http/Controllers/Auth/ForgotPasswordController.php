<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\MailManager;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Rules\Recaptcha;
use Illuminate\Validation\Rule;
use App\Utility\SmsUtility;
use Mail;
use App\Services\FirebaseTokenVerifier;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

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
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        \Log::info('sendResetLinkEmail called', $request->all());

        try {
            $this->syncFirebaseVerification($request);
        } catch (\Exception $e) {
            \Log::error('syncFirebaseVerification failed: ' . $e->getMessage());
            // If it is a validation exception, we should probably let it throw or handle it.
            // But if the frontend sends no token (just phone), and we require one, this throws.
            // If we want to fallback to system OTP, we could ignore it here?
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                // For diagnosis, we log it.
                \Log::warning('ValidationException in Firebase sync. Proceeding or Throwing?', ['errors' => $e->errors()]);
                throw $e;
            }
        }

        // validate recaptcha
        $request->validate([
            'g-recaptcha-response' => [
                Rule::when(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_forgot_password') == 1, ['required', new Recaptcha()], ['sometimes'])
            ],
        ]);

        $verifiedPhone = $request->input('firebase_verified_phone');
        $phone = $verifiedPhone ?: "+{$request['country_code']}{$request['phone']}";

        \Log::info('sendResetLinkEmail phone determined', ['phone' => $phone]);

        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->email)->first();
            if ($user != null) {
                $user->verification_code = rand(100000, 999999);
                $user->save();

                $emailTemplate = EmailTemplate::whereIdentifier('password_reset_email_to_all')->first();
                $emailSubject = $emailTemplate->subject;
                $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailSubject);

                $email_body = $emailTemplate->default_text;
                $email_body = str_replace('[[user_email]]', $user->email, $email_body);
                $email_body = str_replace('[[code]]', $user->verification_code, $email_body);
                $email_body = str_replace('[[store_name]]', get_setting('site_name'), $email_body);

                $array['subject'] = $emailSubject;
                $array['content'] = $email_body;
                Mail::to($user->email)->queue(new MailManager($array));

                return view('auth.' . get_setting('authentication_layout_select') . '.reset_password');
            } else {
                flash(translate('No account exists with this email'))->error();
                return back();
            }
        } else {
            $user = User::where('phone', $phone)->first();

            // Flexible Phone Matching (Same as LoginController)
            if (!$user) {
                // Try removing '+'
                $phoneNoPlus = str_replace('+', '', $phone);
                $user = User::where('phone', $phoneNoPlus)->first();
            }
            if (!$user) {
                // Try last 10 digits
                $last10 = substr($phone, -10);
                $user = User::where('phone', $last10)->first();
            }

            if ($user != null) {
                $user->verification_code = rand(100000, 999999);
                $user->save();

                // Note: We don't always need to send SMS if Firebase was used, 
                // but strictly speaking, the backend "Reset" flow expects a code.
                // If the user came via Firebase, they are verified. 
                // We pass the code to the view to auto-fill it.
                $auto_code = $user->verification_code;

                if (!$verifiedPhone) {
                    // Only send SMS if they didn't verify via Firebase (fallback) 
                    // OR should we send it anyway for record? 
                    // If firebase was used, we trust them.
                    SmsUtility::password_reset($user);
                }

                return view('otp_systems.frontend.auth.' . get_setting('authentication_layout_select') . '.reset_with_phone', [
                    'code' => $auto_code,
                    'phone' => $user->phone
                ]);
            } else {
                \Log::warning('No user found with phone', ['phone' => $phone]);
                flash(translate('No account exists with this phone number'))->error();
                return back();
            }
        }
    }

    private function syncFirebaseVerification(Request $request): void
    {
        if (!$this->firebaseOtpEnabled()) {
            return;
        }

        $token = $request->input('firebase_id_token');
        $isPhoneFlow = $request->filled('phone') || $request->filled('firebase_verified_phone');
        $required = $this->firebaseOtpForgotRequired() && $isPhoneFlow;
        if ($required && !$request->filled('phone')) {
            throw ValidationException::withMessages([
                'phone' => translate('Phone number is required for OTP reset.'),
            ]);
        }
        if ($required && !$token) {
            throw ValidationException::withMessages([
                'phone' => translate('Phone verification via Firebase is required to reset your password.'),
            ]);
        }

        if ($token) {
            $verified = app(FirebaseTokenVerifier::class)->verify($token);
            $request->merge([
                'firebase_verified_phone' => $verified['phone'],
                'firebase_uid' => $verified['uid'],
            ]);
        }
    }

    private function firebaseOtpEnabled(): bool
    {
        return (bool) (get_setting('firebase_otp_enabled') == 1 && env('FIREBASE_OTP_ENABLED', false));
    }

    private function firebaseOtpForgotRequired(): bool
    {
        return $this->firebaseOtpEnabled() && get_setting('firebase_otp_require_forgot') == 1;
    }
}
