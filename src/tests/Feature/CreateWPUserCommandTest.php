<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;

uses(TestCase::class, DatabaseMigrations::class);

it('creates a user with a legacy phpass hash by default', function () {
    $this->artisan('wp:create-wp-user')
        ->expectsOutput('Creating user with legacy phpass hash.')
        ->expectsOutput('User created successfully.')
        ->assertSuccessful();

    $this->assertDatabaseCount('users', 1);

    $user = User::first();
    expect($user->password)->toStartWith('$P$');
});

it('creates a user with a new bcrypt hash when specified', function () {
    $this->artisan('wp:create-wp-user --type=bcrypt')
        ->expectsOutput('Creating user with new WP bcrypt hash.')
        ->expectsOutput('User created successfully.')
        ->assertSuccessful();

    $this->assertDatabaseCount('users', 1);

    $user = User::first();
    expect($user->password)->toStartWith('$wp$2y$');
});

it('returns an error for an invalid hash type', function () {
    $this->artisan('wp:create-wp-user --type=invalid')
        ->expectsOutput('Invalid hash type specified. Use "phpass" or "bcrypt".')
        ->assertExitCode(1);

    $this->assertDatabaseCount('users', 0);
});

it('can log in with created user passwords for each type', function (string $type) {
    // Create a user using the artisan command
    $this->artisan('wp:create-wp-user', ['--type' => $type])->assertSuccessful();

    $user = User::first();
    expect($user)->not->toBeNull();

    // Attempt to log in with the created user's credentials
    $credentials = [
        'email' => $user->email,
        'password' => 'password', // The password used in the command
    ];

    // Assert that the login attempt is successful
    $loginSuccess = Auth::attempt($credentials);
    expect($loginSuccess)->toBeTrue();

    // Assert that the user is authenticated
    $this->assertAuthenticatedAs($user);

})->with([
    'phpass',
    'bcrypt',
]);