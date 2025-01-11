<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Default WordPress Hashing Settings
    |--------------------------------------------------------------------------
    |
    | These options control the default settings used for WordPress-compatible
    | password hashing.
    | php artisan vendor:publish --tag=wp-login-config
    */

    // Iteration count for the password hashing algorithm.
    'iteration_count' => 8,

    // Enable portable hashing (compatible across systems).
    'portable_hashes' => true,

    'wp_connection' => 'wp', // defines the connection name for your WP connection that you defined in config/database.php
];