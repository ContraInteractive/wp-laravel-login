<?php

namespace ContraInteractive\WpLaravelLogin\Auth\UserProviders;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\EloquentUserProvider;
use function ContraInteractive\WpLaravelLogin\Auth\app;
use ContraInteractive\WpLaravelLogin\Auth\WpPasswordHasher;

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
        // The plain-text password from the login form
        $plain = $credentials['password'];

        // The stored (hashed) password in DB
        $existingHash = $user->getAuthPassword();

        // 1) Check if the stored password is a WordPress hash
        $wp = WpPasswordHasher::check($plain, $existingHash);

        if ($wp->CheckPassword($plain, $existingHash)) {
            // If it matches, re-hash with Laravel's default
            $this->rehashPasswordIfRequired($user, $credentials);

            return true;
        }

        // 2) Otherwise, attempt normal Laravel hashing check
        return parent::validateCredentials($user, $credentials);
    }
}