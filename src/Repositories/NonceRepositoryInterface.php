<?php

namespace ContraInteractive\WpLaravelLogin\Repositories;

use Illuminate\Support\Carbon;

interface NonceRepositoryInterface
{
    /**
     * Check if a nonce exists in the store.
     *
     * @param  string $nonce
     * @return bool
     */
    public function exists(string $nonce): bool;

    /**
     * Store a nonce with an expiration time.
     *
     * @param string $nonce
     * @param Carbon $expiresAt
     * @return void
     */
    public function store(string $nonce, Carbon $expiresAt): void;
}