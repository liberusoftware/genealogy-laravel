<?php

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;

it('replays completed idempotent writes and rejects changed payloads', function (): void {
    Route::post('/testing/api-contract/idempotency', static fn (): JsonResponse => response()->json(['created' => true], 201))
        ->middleware(ApiContract::class);

    $headers = ['Idempotency-Key' => 'contract-test-key'];

    $first = $this->withHeaders($headers)->postJson('/testing/api-contract/idempotency', ['name' => 'same']);
    $replay = $this->withHeaders($headers)->postJson('/testing/api-contract/idempotency', ['name' => 'same']);
    $conflict = $this->withHeaders($headers)->postJson('/testing/api-contract/idempotency', ['name' => 'different']);

    $first->assertCreated()->assertJson(['created' => true]);
    $replay->assertCreated()->assertHeader('Idempotency-Replayed', 'true')->assertJson(['created' => true]);
    $conflict->assertStatus(409)->assertHeader('Content-Type', 'application/problem+json');
});

it('supports conditional reads and optimistic updates with entity tags', function (): void {
    Route::get('/testing/api-contract/users/{user}', static fn (User $user): JsonResponse => response()->json(['id' => $user->getKey()]))
        ->middleware(ApiContract::class);
    Route::patch('/testing/api-contract/users/{user}', static fn (User $user): JsonResponse => response()->json(['id' => $user->getKey()]))
        ->middleware(ApiContract::class);

    $user = User::factory()->create();
    $read = $this->getJson('/testing/api-contract/users/'.$user->getKey());
    $etag = $read->headers->get('ETag');

    expect($etag)->toBeString()->not->toBe('');

    $read->assertOk();
    $this->withHeader('If-None-Match', $etag)->getJson('/testing/api-contract/users/'.$user->getKey())->assertStatus(304);
    $this->withHeader('If-Match', $etag)->patchJson('/testing/api-contract/users/'.$user->getKey())->assertOk();
    $this->withHeader('If-Match', '"stale"')->patchJson('/testing/api-contract/users/'.$user->getKey())
        ->assertStatus(412)
        ->assertHeader('Content-Type', 'application/problem+json');
});
