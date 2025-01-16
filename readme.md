## WP Laravel Login

WP Laravel Login is a PHP library that enables seamless user authentication in a Laravel application using existing WordPress hashed passwords.

### Features
- Authenticate Laravel users with WordPress hashed passwords.
- Compatible with existing WordPress installations.
- Simplifies integration for WordPress and Laravel hybrid applications.

Installation  
You can install the package via composer:

```bash  
composer require contrainteractive/wp-laravel-login  
```  

### Usage

#### There is multiple ways to use this plugin.

1. **Migrate from WP to Laravel**:
   - This is the recommended way to use the package. It provides a custom user provider that validates user credentials against WordPress hashed passwords.
   - The package also includes an artisan command to copy WordPress users to Laravel.
   - Simply import your users from WP with their existing hashes and then users auth into your new Laravel Environment

2. **Wordpress/Laravel Hybrid**
   - This is where you might have Laravel and WP running side-by-side for a given time
   - In this scenario, still import your users into Laravel
   - When a user updates their password on Wordpress invoke the provided API sync route
   - See `Syncing Passwords` below


### Docs

**WordPress User Authentication**

The package provides a custom `WpUserProvider` that validates credentials by:
1.	Validates passwords against the WordPress hashing format.
2.	Updates passwords to Laravel’s hashing mechanism upon successful login (unless disabled).


Update auth.php to use the custom provider:
```php  
'providers' => [  
   'users' => [  
      'driver' => 'wp',  //<-- laravel auth still works as normal here
      'model' => env('AUTH_MODEL', App\Models\User::class),  
 ],
```  
**Password Rehashing Behavior**

By default, the package updates the password hash to Laravel’s format after login. To disable this, update the config/wp-login.php file:

```php    
// config/wp-login.php  
// Enable or disable the preservation of the WordPress hash after user login. 
return [  
 'preserve_wp_hash' => false,  
];  
```  
- There isn't a known use case to me where this would ever be true, but you are welcome to use it.

### Copy WordPress Users to Laravel
Included is a basic artisan command to copy WordPress users to Laravel. This is useful for migrating users from WordPress to Laravel.

```bash  
php artisan wp:copy-users --table-prefix=custom_wp --host=localhost --database=my_wp_db --username=admin --password=password123  
```  

You can also define a db connection in the `config/database.php` file instead of command arguments. Which is a more friendly way of using this package.  The `wp:copy-users` command will automatically use it

```php 
  'wp' => 
	  [ 
            'driver'    => 'mysql', 
            'host'      => env('WP_DB_HOST', '127.0.0.1'), 
            'database'  => env('WP_DB_DATABASE', 'wordpress'), 
            'username'  => env('WP_DB_USERNAME', 'root'), 
            'password'  => env('WP_DB_PASSWORD', ''), 
            'charset'   => 'utf8mb4', 
            'collation' => 'utf8mb4_unicode_ci', 
            'prefix'    => env('WP_DB_PREFIX', 'wp_'),
	  ],
```  

Fetch and copy WordPress users programmatically:


```php  
$users = DB::connection('wp')  
 ->table('wp_users')  
 ->select('ID', 'user_login', 'user_pass', 'user_email', 'user_registered')  
 ->get();  
  
// You might then Copy WordPress users to Laravel  
// if you do insert()/save() a user into Laravel.  Know that larvel likes to auto-hash
// So Compare your WP DB against your Larvel DB to make sure the passwords match after initial import

foreach ($wpUsers as $wpUser) {  
    try {  
        DB::table('users')->updateOrInsert(  
			['email' => $wpUser->user_email],  
			[ 
				'name'       => $wpUser->user_login, 
				'email'      => $wpUser->user_email, 
				'password'   => $wpUser->user_pass,// retain wp hash
				'created_at' => $wpUser->user_registered, 
				'updated_at' => now(), 
			]  
		);  
  
		 $this->info("Copied user: {$wpUser->user_login}"); 
	 } catch (\Exception $e) { 
		 $this->error("Failed to copy user {$wpUser->user_login}: " . $e->getMessage()); 
	 }
 }
```  

You can also just create a wordpress user as a test with this :

```php  
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ContraInteractive\WpLaravelLogin\Auth\Hashers\WP\PasswordHash;

class CreateWPUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-wp-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // make a user with the WordPress password hash
        $password = 'password';
        $wp = app(PasswordHash::class, ['iteration_count_log2' => 8, 'portable_hashes' => true]);
        $hash = $wp->HashPassword($password);

        $user = new \App\Models\User();

        $user->setRawAttributes([
            'password' => $hash, // <-- take the hash as is
            'name' => 'John Doe',
            'email' => 'example'.rand(1, 1000).time() .'@example.com',
        ]);

        $user->save();
    }
}
```



### Publishing the Configuration

To customize the settings for WordPress password hashing and database connection, you can publish the package configuration file using the following Artisan command:

```bash  
php artisan vendor:publish --tag=wp-login-config  
```  

#### Configuration Options  (not all of them)

The configuration file includes the following options:
-   **iteration_count**: Defines the iteration count for the WordPress-compatible password hashing algorithm.   
    Default: 8

-   **portable_hashes**: Enables portable hashing for compatibility across systems.   
    Default: true

-   **wp_connection**: Specifies the name of the WordPress database connection defined in config/database.php.   
    Default: 'wp'

### Here’s an example of what the published configuration file (`config/wp-login.php`) might look like:

```php  
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
```
### Syncing Passwords to Laravel (WP => Laravel)
1. For starters lets be uber careful here.  We are sending a hashed password to an api endpoint.  Use HTTPS
2.  `'sync_password_route'  =>  '/api/wp-sync-password',` in the config defines the route
3.  A premade `RestrictIp::class` is provided.  Enable that in the middleware setting `sync_password_middleware`
4. Set what IP's can hit this route in `sync_password_allowed_ips`
5. Define a `WP_SHARED_SECRET` in both WP and Laravel (used when posting from WP)
6. Post the updated password from WP to larvel.  Which allows a user to auth in both platforms

**If you want to call this endpoint from WP **
```php
/**

* Hook into the 'profile_update' action to detect when a user's password changes.
* @param int  $user_id The user ID being updated.
* @param stdClass $old_user_data An object containing the user's data before update.

*/

add_action( 'profile_update', 'check_and_send_password_update', 10, 2 );

function check_and_send_password_update( $user_id, $old_user_data ) {

	// Get the updated user object
	$user = get_userdata( $user_id );

	// Compare old password vs. new password. If different, the password changed.
	if ( $old_user_data->user_pass !== $user->user_pass ) {
		// The password has changed, so let's send an update to Laravel
		$email = $user->user_email;
		$wp_hash = $user->user_pass; // This is the hashed password in WP

		// Call our function that sends the updated password to Laravel
		send_password_update_to_laravel( $email, $wp_hash );
	}
}

/**

* Example WordPress function to send password update to Laravel
* @param string $email  The user’s email.
* @param string $wp_hash  The user’s (already hashed) password from WP.
* @return void
*/

function send_password_update_to_laravel( $email, $wp_hash ) {

	// 1. Generate a nonce (16 bytes => 32 hex chars)
	$nonce = bin2hex( random_bytes(16) );

	// 2. Get timestamp (in seconds)
	$timestamp = time();

	// 3. Prepare payload
	$payload = [
		'email' => $email,
		'wp_hash' => $wp_hash,
		'nonce' => $nonce,
		'timestamp' => $timestamp,
	];

	// 4. Compute HMAC signature
	$secret = 'your_shared_secret_goes_here'; // Make sure to store this securely
	$signature = hash_hmac('sha256', json_encode($payload), $secret);

	// 5. Send request to Laravel
	$response = wp_remote_post('https://your-laravel-app.com/api/wp-sync-password',
	[
		'headers' => [
			'Content-Type'  => 'application/json',
			'X-Signature' => $signature, // Custom header for the signature
		],

		'body'  => json_encode($payload),
	]
	);

	if ( is_wp_error( $response ) ) {
		// Handle connection error
		error_log( 'Error updating password on Laravel side: ' . $response->get_error_message() );
	} else {
		// Optional: Check the response code/body
		$status_code = wp_remote_retrieve_response_code( $response );
		$body  = wp_remote_retrieve_body( $response );

		// Debug or log as needed
		// error_log( 'Laravel response code: ' . $status_code );
		// error_log( 'Laravel response body: ' . $body );

	}
}
```
**There is also a postman collection included in this package on the root dir**
- wp-laravel-login.postman_collection.json

**This script for postman should give you some idea of what is involved**
- in headers define a `X-Signature` with a value of `{{signature}}`
- in the raw post body use `{{payload}}`
```javascript
pm.variables.set("timestamp", Math.floor(Date.now() /  1000));

const payload = {
	email: "user@example.com",
	wp_hash: "somehashvalue",
	nonce: "somenonce", // must be unique each time
	timestamp: Math.floor(Date.now() /  1000) // Current timestamp
};

// Define the secret WP_SHARED_SECRET
const secret =  "+vz40v479IC/YZII2ANuXPJSgBbU4a/x";

// Stringify the payload
const payloadString =  JSON.stringify(payload);

// Generate the HMAC signature
const crypto =  require('crypto-js'); // Postman supports CryptoJS library
const signature = crypto.HmacSHA256(payloadString, secret).toString(crypto.enc.Hex);

// Set the payload and signature as variables
pm.variables.set("payload", JSON.stringify(payload));
pm.variables.set("signature", signature);
```