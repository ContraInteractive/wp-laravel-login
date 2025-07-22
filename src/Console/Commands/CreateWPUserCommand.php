<?php

namespace ContraInteractive\WpLaravelLogin\Console\Commands;

use Illuminate\Console\Command;
use ContraInteractive\WpLaravelLogin\Auth\Hashers\WP\PasswordHash;


class CreateWPUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wp:create-wp-user 
                            {--type=phpass : The hashing algorithm to use (phpass or bcrypt)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user with a WordPress-compatible password hash.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $password = 'password'; // For demonstration purposes
        $type = $this->option('type');
        $hash = '';

        if ($type === 'bcrypt') {
            // Create a WordPress 6.8+ compatible bcrypt hash.
            $password_to_hash = base64_encode(hash_hmac('sha384', $password, 'wp-sha384', true));
            $hash = '$wp' . password_hash($password_to_hash, PASSWORD_BCRYPT);
            $this->info('Creating user with new WP bcrypt hash.');
        } elseif ($type === 'phpass') {
            // Create a legacy phpass hash.
            $wp_hasher = app(PasswordHash::class, ['iteration_count_log2' => 8, 'portable_hashes' => true]);
            $hash = $wp_hasher->HashPassword($password);
            $this->info('Creating user with legacy phpass hash.');
        } else {
            $this->error('Invalid hash type specified. Use "phpass" or "bcrypt".');
            return 1;
        }

        $user = new \App\Models\User();

        $user->setRawAttributes([
            'password' => $hash,
            'name' => 'John Doe-'.$type,
            'email' => 'example'.rand(1, 1000).time() .'@example.com',
        ]);

        $user->save();

        $this->info('User created successfully.');
        return 0;
    }
}