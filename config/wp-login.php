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

    // Enable or disable the preservation of the WordPress hash after user login.
    'preserve_wp_hash' => false,

    // defines the connection name for your WP database connection that you defined in config/database.php
    // if your wp & laravel are on the same server this is easy.  Otherwise you need to allow remote/external connections to your WP database
    'wp_connection' => 'wp',


    /*
    |--------------------------------------------------------------------------
    | Shared Secret
    |--------------------------------------------------------------------------
    |   The shared secret for the WordPress site to call API methods. This should be the same as the WP_SHARED_SECRET constant on your WordPress site.
    |   When sending api requests from the WordPress site, the shared secret will be used to sign the request.
    |    $payload = [
    |    'email'     => $email, // the email of the user
    |    'wp_hash'   => $wp_hash, // the hashed password in the WP DB
    |    'nonce'     => $nonce, // random string (only used once)
    |    'timestamp' => $timestamp, // time()
    |    ];
    |
    |   $signature = hash_hmac('sha256', json_encode($payload), $secret);
    |
    |     [ // example headers for the request
    |       'headers' => [
    |            'Content-Type'  => 'application/json',
    |            'X-Signature'   => $signature, // the hash_hmac signature
    |        ],
    |        'body'    => json_encode($payload),
    |    ]
    */

    'shared_secret' => env('WP_SHARED_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Sync Password Route options
    |--------------------------------------------------------------------------
    | The route to sync the password from WordPress to Laravel
    | The middleware to use for the route
    | The allowed IPs that can access the route (if using RestrictIp::class) otherwise ignored
    | A premade restricction is here :  \ContraInteractive\WpLaravelLogin\Middleware\RestrictIp::class
    */

    'sync_password_route' => '/api/wp-sync-password',
    'sync_password_middleware' => ['api'],  //['api', \ContraInteractive\WpLaravelLogin\Middleware\RestrictIp::class],
    'sync_password_allowed_ips' => ['localhost','localhost:3000','127.0.0.1','127.0.0.1:8000','::1'],
];