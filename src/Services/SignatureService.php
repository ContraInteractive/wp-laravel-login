<?php

namespace ContraInteractive\WpLaravelLogin\Services;


use ContraInteractive\WpLaravelLogin\Exceptions\InvalidSignatureException;

class SignatureService
{
    public function verifySignature(array $payload, string $signature): void
    {
        // Get your shared secret from config
        $secret = config('wp-login.shared_secret');

        $computedSignature = hash_hmac('sha256', json_encode($payload), $secret);

        if (!hash_equals($computedSignature, $signature)) {
            throw new InvalidSignatureException('Invalid HMAC signature.');
        }
    }
}