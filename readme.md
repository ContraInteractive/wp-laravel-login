## WP Laravel Login

WP Laravel Login is a PHP library that enables seamless user authentication in a Laravel application using existing WordPress hashed passwords.

### Features
- Authenticate Laravel users with WordPress hashed passwords.
- 	Compatible with existing WordPress installations.
- 	Simplifies integration for WordPress and Laravel hybrid applications.

Installation
You can install the package via composer:

```bash
composer require contrainteractive/wp-laravel-login
```

### Usage

WordPress User Authentication

The package provides a custom WpUserProvider that validates credentials by:
1.	Checking if the stored password is a WordPress hash.
2.	Rehashing the password to Laravel’s hashing mechanism upon successful login.

Ensure the auth.php configuration uses the custom provider:
    
```php
'providers' => [
    'users' => [
    'driver' => 'wp', //<-- Custom provider. Still works with the default Eloquent provider. 
    'model' => env('AUTH_MODEL', App\Models\User::class),
],
```
3. !! Note the Users password will be hashed by laravel after login. It updates the password in the database.

### Copy WordPress Users to Laravel
Included is a basic artisan command to copy WordPress users to Laravel. This is useful for migrating users from WordPress to Laravel.

```bash
php artisan wp:copy-users --table-prefix=custom_wp --host=localhost --database=my_wp_db --username=admin --password=password123
```

You can also define a db connection in the config/database.php file and use the connection name as an argument.

```bash
 'wp' => [
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

Which means you can do:

```bash
$users = DB::connection('wp')
    ->table('wp_users')
    ->select('ID', 'user_login', 'user_pass', 'user_email', 'user_registered')
    ->get();
```
And build your own migration script.


### Publishing the Configuration

To customize the settings for WordPress password hashing and database connection, you can publish the package configuration file using the following Artisan command:
    
```bash
php artisan vendor:publish --tag=wp-login-config
```

#### Configuration Options

The configuration file includes the following options:
-	**iteration_count**: Defines the iteration count for the WordPress-compatible password hashing algorithm. 
Default: 8

-	**portable_hashes**: Enables portable hashing for compatibility across systems. 
Default: true

-	**wp_connection**: Specifies the name of the WordPress database connection defined in config/database.php. 
Default: 'wp'

### Here’s an example of what the published configuration file (config/wp-login.php) might look like:
    
```php
return [
    'iteration_count' => 8,
    'portable_hashes' => true,
    'wp_connection' => 'wp',
];
```