<?php

namespace ContraInteractive\WpLaravelLogin\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class WpDatabaseManager
{
    /**
     * Ensure the WordPress database connection is configured.
     *
     * @param array $connectionDetails
     * @param string $connectionName
     * @return void
     */
    public static function ensureConnection(array $connectionDetails, string $connectionName = 'wp'): void
    {
        // Check if connection already exists
        if (Config::has("database.connections.{$connectionName}")) {
            return;
        }

        // Set the new connection dynamically
        Config::set("database.connections.{$connectionName}", array_merge([
            'driver'    => 'mysql',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ], $connectionDetails));
    }

    /**
     * Test the specified database connection.
     *
     * @param string $connectionName
     * @return bool
     */
    public static function testConnection(string $connectionName = 'wp'): bool
    {
        try {
            DB::connection($connectionName)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}