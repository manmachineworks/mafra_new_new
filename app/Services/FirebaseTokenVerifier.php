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
        } catch (FailedToVerifyToken $e) {
            // Throwing an exception that can be caught by the global handler or controller
            throw new \Exception('Invalid Firebase ID Token: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Firebase Authentication Failed: ' . $e->getMessage());
        }
    }
}
