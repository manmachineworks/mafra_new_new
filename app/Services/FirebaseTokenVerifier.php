<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Throwable;

class FirebaseTokenVerifier
{
    public function __construct(private ?string $projectId = null)
    {
        $this->projectId = $this->projectId ?: env('FIREBASE_PROJECT_ID');
    }

    /**
     * Validate a Firebase ID token and return its decoded payload.
     *
     * @throws \InvalidArgumentException when token is invalid
     */
    public function verify(string $idToken): array
    {
        if (empty($this->projectId)) {
            throw new \InvalidArgumentException('Missing Firebase project id.');
        }

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText(''),
            $this->publicKeyFromFirebase($idToken)
        );

        $token = $config->parser()->parse($idToken);

        $config->validator()->assert(
            $token,
            new IssuedBy('https://securetoken.google.com/' . $this->projectId),
            new PermittedFor($this->projectId),
            new LooseValidAt(SystemClock::fromUTC())
        );

        return [
            'uid' => $token->claims()->get('sub'),
            'phone' => $token->claims()->get('phone_number'),
            'claims' => $token->claims()->all(),
        ];
    }

    private function publicKeyFromFirebase(string $idToken): InMemory
    {
        $kid = $this->readKid($idToken);
        $keys = Cache::remember('firebase_public_keys', 55, function () {
            $response = Http::get('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com');
            if (!$response->successful()) {
                throw new \InvalidArgumentException('Unable to fetch Firebase public keys.');
            }
            return $response->json();
        });

        if (!is_array($keys) || !isset($keys[$kid])) {
            throw new \InvalidArgumentException('Firebase public key not found for token.');
        }

        return InMemory::plainText($keys[$kid]);
    }

    private function readKid(string $idToken): string
    {
        $parts = explode('.', $idToken);
        if (count($parts) < 2) {
            throw new \InvalidArgumentException('Malformed Firebase ID token.');
        }

        $header = json_decode(base64_decode($parts[0]), true);
        if (!isset($header['kid'])) {
            throw new \InvalidArgumentException('Missing key id in token header.');
        }

        return $header['kid'];
    }
}
