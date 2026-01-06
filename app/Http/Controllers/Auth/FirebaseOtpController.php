<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseTokenVerifier;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FirebaseOtpController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('attachPhoneToUser');
    }

    public function verifyPhone(Request $request, FirebaseTokenVerifier $verifier): JsonResponse
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
        ]);
        $verified = $this->attemptVerification($verifier, $data['id_token']);

        // cache verification in session for next request (registration/login forms)
        session([
            'firebase_verified_phone' => $verified['phone'],
            'firebase_uid' => $verified['uid'],
            'firebase_verified_at' => now(),
        ]);

        return response()->json([
            'phone' => $verified['phone'],
            'firebase_uid' => $verified['uid'],
            'verified_at' => now()->toIso8601String(),
        ]);
    }

    public function attachPhoneToUser(Request $request, FirebaseTokenVerifier $verifier): JsonResponse
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
        ]);
        $verified = $this->attemptVerification($verifier, $data['id_token']);

        /** @var User $user */
        $user = $request->user();
        $user->forceFill([
            'phone' => $verified['phone'],
            'firebase_uid' => $verified['uid'],
            'phone_verified_at' => Carbon::now(),
        ])->save();

        return response()->json([
            'message' => translate('Phone number verified and linked.'),
            'phone' => $verified['phone'],
            'firebase_uid' => $verified['uid'],
        ]);
    }

    private function validateToken(FirebaseTokenVerifier $verifier, string $idToken): array
    {
        $verified = $verifier->verify($idToken);

        if (empty($verified['phone'])) {
            throw new \InvalidArgumentException('No phone number found in Firebase token.');
        }

        return $verified;
    }

    private function attemptVerification(FirebaseTokenVerifier $verifier, string $idToken): array
    {
        try {
            return $this->validateToken($verifier, $idToken);
        } catch (\Throwable $e) {
            throw new HttpResponseException(
                response()->json([
                    'message' => translate('Invalid or expired Firebase ID token.'),
                    'details' => app()->environment('local') ? $e->getMessage() : null,
                ], 422)
            );
        }
    }
}
