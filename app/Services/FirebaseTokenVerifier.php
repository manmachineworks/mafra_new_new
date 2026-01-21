<?php

namespace App\Services;

use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Contract\Auth;

class FirebaseTokenVerifier
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Verify a Firebase ID Token.
     *
     * @param  string  $token
     * @return array
     */
    public function verify($token)
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($token);
            $uid = $verifiedIdToken->claims()->get('sub');
            $user = $this->auth->getUser($uid);

            return [
                'uid' => $uid,
                'phone' => $user->phoneNumber,
            ];
        } catch (\Exception $e) {
            // [DEV SAFE-MODE] FALLBACK FOR LOCAL DEVELOPMENT ONLY
            // Since the real 'firebase-admin.json' credentials are often missing in local/dev environments,
            // this fallback allows the login flow to continue by decoding the token insecurely.
            // SECURITY NOTE: This block ONLY executes if APP_ENV is 'local'. It is disabled in production.
            if (app()->environment('local')) {
                \Log::warning('Firebase verification failed (missing credentials?), using LOCAL FALLBACK. Error: ' . $e->getMessage());

                $parts = explode('.', $token);
                if (count($parts) === 3) {
                    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
                    if ($payload && isset($payload['phone_number'])) {
                        return [
                            'uid' => $payload['user_id'] ?? $payload['sub'] ?? 'local-uid',
                            'phone' => $payload['phone_number'],
                        ];
                    }
                }
            }

            // Throwing an exception that can be caught by the global handler or controller
            throw new \Exception('Invalid Firebase ID Token: ' . $e->getMessage());
        }
    }
}
