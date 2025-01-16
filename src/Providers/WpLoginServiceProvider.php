<?php

namespace ContraInteractive\WpLaravelLogin\Providers;

use Illuminate\Support\ServiceProvider;

use ContraInteractive\WpLaravelLogin\Console\Commands\CopyWpUsersCommand;
use ContraInteractive\WpLaravelLogin\Auth\UserProviders\WpUserProvider;
use ContraInteractive\WpLaravelLogin\Console\Commands\CreateWPUserCommand;
use ContraInteractive\WpLaravelLogin\Repositories\NonceRepositoryInterface;
use ContraInteractive\WpLaravelLogin\Repositories\CacheNonceRepository;

class WpLoginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Merge the default configuration
        $this->mergeConfigFrom(__DIR__ . '/../../config/wp-login.php', 'wp-login');

        // Bind the NonceRepositoryInterface to the CacheNonceRepository
        $this->app->bind(NonceRepositoryInterface::class, CacheNonceRepository::class);
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
                CreateWPUserCommand::class,
            ]);
        }
        $this->loadRoutesFrom(__DIR__.'/../../src/Routes/wp-laravel-login.php');
    }
}