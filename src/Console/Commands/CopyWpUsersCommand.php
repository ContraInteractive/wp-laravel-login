<?php

namespace ContraInteractive\WpLaravelLogin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use ContraInteractive\WpLaravelLogin\Services\WpDatabaseManager;

class CopyWpUsersCommand extends Command
{
    //  php artisan wp:copy-users --table-prefix=custom_wp
    // php artisan wp:copy-users --host=127.0.0.1 --database=wordpress --username=root --password=secret


    protected $signature = 'wp:copy-users 
                            {--host= : The database host for the WordPress database} 
                            {--database= : The WordPress database name} 
                            {--username= : The database username} 
                            {--password= : The database password} 
                            {--table-prefix=wp_ : The WordPress table prefix (default: wp_)}';

    protected $description = 'Copy WordPress users into Laravel database';

    public function handle()
    {
        // Get the table prefix, or use the default
        $tablePrefix = $this->option('table-prefix') ?? 'wp_';

        // Ensure WordPress connection with command-line options having the highest priority
        $connectionConfig = config('wp-login.wp_connection', 'wp');
        WpDatabaseManager::ensureConnection($this->collectConnectionDetails($connectionConfig));

        // Test the connection
        if (!WpDatabaseManager::testConnection($connectionConfig)) {
            $this->error('Could not connect to the WordPress database.');
            return 1;
        }

        $this->info('Successfully connected to the WordPress database.');

        // Fetch and migrate users
        $wpUsers = DB::connection($connectionConfig)
            ->table("{$tablePrefix}users")
            ->select('ID', 'user_login', 'user_pass', 'user_email', 'user_registered')
            ->get();

        if ($wpUsers->isEmpty()) {
            $this->info('No users found in the WordPress database.');
            return 0;
        }

        $this->info("Found " . $wpUsers->count() . " users. Copying...");

        foreach ($wpUsers as $wpUser) {
            try {
                DB::table('users')->updateOrInsert(
                    ['email' => $wpUser->user_email],
                    [
                        'name'       => $wpUser->user_login,
                        'email'      => $wpUser->user_email,
                        'password'   => $wpUser->user_pass, // Retain the WP hash
                        'created_at' => $wpUser->user_registered,
                        'updated_at' => now(),
                    ]
                );

                $this->info("Copied user: {$wpUser->user_login}");
            } catch (\Exception $e) {
                $this->error("Failed to copy user {$wpUser->user_login}: " . $e->getMessage());
            }
        }

        $this->info('User migration completed successfully.');
        return 0;
    }

    /**
     * Collect connection details with command-line arguments prioritized.
     *
     * @param string $connectionName
     * @return array
     */
    private function collectConnectionDetails(string $connectionName): array
    {
        // Get existing connection details from config
        $existingConnection = config("database.connections.{$connectionName}", []);

        // Override existing config with command-line options
        return [
            'host'     => $this->option('host') ?? $existingConnection['host'] ?? $this->ask('Database host'),
            'database' => $this->option('database') ?? $existingConnection['database'] ?? $this->ask('Database name'),
            'username' => $this->option('username') ?? $existingConnection['username'] ?? $this->ask('Database username'),
            'password' => $this->option('password') ?? $existingConnection['password'] ?? $this->ask('Database password'),
        ];
    }
}