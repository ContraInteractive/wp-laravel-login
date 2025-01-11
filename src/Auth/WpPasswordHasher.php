<?php

namespace ContraInteractive\WpLaravelLogin\Auth;

use ContraInteractive\WpLaravelLogin\Auth\Hashers\WP\PasswordHash;

class WpPasswordHasher
{
    public static function check($password, $hash)
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