<?php

namespace ContraInteractive\WpLaravelLogin\Services;

use ContraInteractive\WpLaravelLogin\Auth\Hashers\WP\PasswordHash;
use function ContraInteractive\WpLaravelLogin\Auth\app;
use function ContraInteractive\WpLaravelLogin\Auth\config;

class WpPasswordHashService
{
    public static function check($password, $hash): bool
    {
        $iterationCount = config('wp-login.iteration_count', 8);
        $portableHashes = config('wp-login.portable_hashes', true);
        $wpHasher = app(
            PasswordHash::class,
            [
                'iteration_count_log2' => $iterationCount,
                'portable_hashes' => $portableHashes
            ]
        );
        return $wpHasher->CheckPassword($password, $hash);
    }
}