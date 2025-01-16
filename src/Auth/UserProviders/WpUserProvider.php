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

        // If password matches the WP hash
        if (WpPasswordHashService::check($plain, $existingHash)) {

            // Rehash with Laravel’s hasher only if config says we *should* rehash
            if (! config('wp-login.preserve_wp_hash')) {
                $this->rehashPasswordIfRequired($user, $credentials);
            }

            return true;
        }

        // Otherwise do the normal Laravel check
        return parent::validateCredentials($user, $credentials);
    }
}