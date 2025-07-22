<?php

namespace ContraInteractive\WpLaravelLogin\Auth\UserProviders;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\EloquentUserProvider;
use function ContraInteractive\WpLaravelLogin\Auth\app;
use ContraInteractive\WpLaravelLogin\Services\WpPasswordHashService;

class WpUserProvider extends EloquentUserProvider
{

    /**
     * Validate user credentials.
     *
     * @param  Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];
        $existingHash = $user->getAuthPassword();

        // Handle new WordPress 6.8+ prefixed bcrypt hashes
        if (str_starts_with($existingHash, '$wp')) {
            // Pre-hash the password the same way WP 6.8 does
            $passwordToVerify = base64_encode(hash_hmac('sha384', $plain, 'wp-sha384', true));
            // Verify against the hash with the prefix removed
            if (password_verify($passwordToVerify, substr($existingHash, 3))) {
                $this->rehashPasswordIfRequired($user, $credentials);
                return true;
            }
        }

        // Check for standard PHP password hashes (e.g., vanilla bcrypt)
        // The password_verify function in PHP is algorithm-agnostic. It automatically detects the algorithm (bcrypt, Argon2id, etc.) from the hash string itself and uses the correct method to verify the password.
        if (password_verify($plain, $existingHash)) {
            if (password_needs_rehash($existingHash, PASSWORD_DEFAULT)) {
                $this->rehashPasswordIfRequired($user, $credentials);
            }
            return true;
        }

        // Fallback for older WordPress phpass hashes
        if (WpPasswordHashService::check($plain, $existingHash)) {
            if (! config('wp-login.preserve_wp_hash')) {
                $this->rehashPasswordIfRequired($user, $credentials);
            }
            return true;
        }

        return false;
    }
}