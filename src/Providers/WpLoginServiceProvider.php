<?php

namespace ContraInteractive\WpLaravelLogin\Providers;

use Illuminate\Support\ServiceProvider;
use ContraInteractive\WpLaravelLogin\Auth\WpUserProvider;
use ContraInteractive\WpLaravelLogin\Console\Commands\CopyWpUsersCommand;

class WpLoginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Merge the default configuration
        $this->mergeConfigFrom(__DIR__ . '/../../config/wp-login.php', 'wp-login');
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Publish the configuration file for customization
        $this->publishes([
            __DIR__ . '/../../config/wp-login.php' => config_path('wp-login.php'),
        ], 'wp-login-config');

        // Register the custom user provider
        $this->app['auth']->provider('wp', function ($app, array $config) {
            return new WpUserProvider($app['hash'], $config['model']);
        });

        // Register the Artisan command if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                CopyWpUsersCommand::class,
            ]);
        }
    }
}