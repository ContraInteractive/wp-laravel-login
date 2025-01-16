<?php

namespace ContraInteractive\WpLaravelLogin\Services;

use ContraInteractive\WpLaravelLogin\Repositories\NonceRepositoryInterface;
use ContraInteractive\WpLaravelLogin\Exceptions\RequestExpiredException;
use ContraInteractive\WpLaravelLogin\Exceptions\NonceAlreadyUsedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use function Laravel\Prompts\password;

class WpSyncPasswordService
{

    public function __construct(
        protected SignatureService $signatureService,
        protected NonceRepositoryInterface $nonceRepo,     // to check & store nonce
    ) {
    }

    /**
     * @param  array  $payload   (keys: email, wp_hash, nonce, timestamp)
     * @param  string $signature (from X-Signature header)
     * @return void
     */
    public function sync(array $payload, string $signature): void
    {
        // 1. Verify Signature
        $this->signatureService->verifySignature($payload, $signature);

        // 2. Check Timestamp (5-minute window)
        $timestamp = (int) ($payload['timestamp'] ?? 0);
        $current = time();
        if (abs($current - $timestamp) > 300) {
            throw new RequestExpiredException('Request timestamp is too old or too far in the future.');
        }

        // 3. Check Nonce
        $nonce = $payload['nonce'] ?? '';
        if ($this->nonceRepo->exists($nonce)) {
            throw new NonceAlreadyUsedException('Nonce already used.');
        }
        // Mark nonce as used
        // e.g. store in cache with TTL or in DB
        $this->nonceRepo->store($nonce, Carbon::now()->addMinutes(10));

        // 4. Update User Password
        $email   = $payload['email'];
        $wp_hash = $payload['wp_hash'];

        $user = DB::table('users')->where('email', $payload['email'])->first();

        if(!$user){
            throw new \Exception('User not found');
        }

        DB::table('users')
            ->where('email', $email)
            ->update(['password' => $wp_hash]);

    }
}