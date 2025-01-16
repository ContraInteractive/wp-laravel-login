<?php

namespace ContraInteractive\WpLaravelLogin\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CacheNonceRepository implements NonceRepositoryInterface
{
    public function exists(string $nonce): bool
    {
        return Cache::has($this->buildKey($nonce));
    }

    public function store(string $nonce, Carbon $expiresAt): void
    {
        Cache::put($this->buildKey($nonce), true, $expiresAt);
    }

    protected function buildKey(string $nonce): string
    {
        return 'used_nonce_' . $nonce;
    }
}