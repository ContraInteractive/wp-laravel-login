<?php

namespace ContraInteractive\WpLaravelLogin\Console\Commands;

use Illuminate\Console\Command;
use ContraInteractive\WpLaravelLogin\Auth\Hashers\WP\PasswordHash;
use function App\Console\Commands\app;

class CreateWPUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wp:create-wp-user';

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
            'password' => $hash,
            'name' => 'John Doe',
            'email' => 'example'.rand(1, 1000).time() .'@example.com',
        ]);

        $user->save();
    }
}
