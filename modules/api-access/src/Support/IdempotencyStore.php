<?php

namespace Liberu\Foundation\ApiAccess\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class IdempotencyStore
{
    public function begin(string $identity, string $key, string $requestBody): ?object
    {
        $hash = hash('sha256', $requestBody);
        DB::table('api_idempotency_keys')
            ->where('identity_ref', $identity)
            ->where('key', $key)
            ->where('expires_at', '<=', now())
            ->delete();

        $existing = DB::table('api_idempotency_keys')
            ->where('identity_ref', $identity)
            ->where('key', $key)
            ->where('expires_at', '>', now())
            ->first();

        if ($existing && ! hash_equals($existing->request_hash, $hash)) {
            throw new RuntimeException('Idempotency key was reused with a different request.');
        }if ($existing) {
            return $existing;
        }

        try {
            DB::table('api_idempotency_keys')->insert([
                'identity_ref' => $identity,
                'key' => $key,
                'request_hash' => $hash,
                'expires_at' => now()->addHours((int) config('api-access.idempotency_hours', 24)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            $existing = DB::table('api_idempotency_keys')
                ->where('identity_ref', $identity)
                ->where('key', $key)
                ->where('expires_at', '>', now())
                ->first();

            if ($existing === null) {
                throw $exception;
            }

            if (! hash_equals($existing->request_hash, $hash)) {
                throw new RuntimeException('Idempotency key was reused with a different request.');
            }

            return $existing;
        }

        return null;
    }

    public function complete(string $identity, string $key, int $status, string $body): void
    {
        DB::table('api_idempotency_keys')->where('identity_ref', $identity)->where('key', $key)->update(['response_status' => $status, 'response_body' => $body, 'updated_at' => now()]);
    }

    public function forget(string $identity, string $key): void
    {
        DB::table('api_idempotency_keys')
            ->where('identity_ref', $identity)
            ->where('key', $key)
            ->delete();
    }
}
