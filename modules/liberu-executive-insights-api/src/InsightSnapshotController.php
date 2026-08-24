<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Platform\ExecutiveInsights\Actions\CreateInsightSnapshot;
use Liberu\Platform\ExecutiveInsights\Actions\TransitionInsightSnapshot;
use Liberu\Platform\ExecutiveInsights\Enums\LifecycleStatus;
use Liberu\Platform\ExecutiveInsights\Models\InsightSnapshot;

final class InsightSnapshotController
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);
        $perPage = min(max((int) $request->input('page.size', 25), 1), 100);

        return response()->json(['data' => InsightSnapshot::query()->forTenant($tenantId)->latest()->paginate($perPage)]);
    }

    public function store(Request $request, CreateInsightSnapshot $create): JsonResponse
    {
        $tenantId = $this->tenantId($request);
        $idempotencyKey = $request->header('Idempotency-Key');
        abort_unless(is_string($idempotencyKey) && $idempotencyKey !== '' && strlen($idempotencyKey) <= 100, 422, 'A valid Idempotency-Key header is required.');

        $existing = InsightSnapshot::query()->forTenant($tenantId)->where('idempotency_key', $idempotencyKey)->first();
        if ($existing !== null) {
            return response()->json(['data' => $existing]);
        }

        $record = $create->execute([
            'tenant_id' => $tenantId,
            'idempotency_key' => $idempotencyKey,
            ...$request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', LifecycleStatus::class],
            'metadata' => ['nullable', 'array'],
            ]),
        ]);

        return response()->json(['data' => $record], 201);
    }

    public function show(Request $request, InsightSnapshot $record): JsonResponse
    {
        $this->assertTenant($request, $record);

        return response()->json(['data' => $record]);
    }

    public function update(Request $request, InsightSnapshot $record, TransitionInsightSnapshot $transition): JsonResponse
    {
        $this->assertTenant($request, $record);
        $attributes = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', LifecycleStatus::class],
            'metadata' => ['nullable', 'array'],
        ]);
        $status = $attributes['status'] ?? null;
        unset($attributes['status']);
        $record->update($attributes);
        if ($status !== null && $status !== $record->status) {
            $transition->execute($record, LifecycleStatus::from($status));
        }

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(Request $request, InsightSnapshot $record): JsonResponse
    {
        $this->assertTenant($request, $record);
        $record->delete();

        return response()->json(status: 204);
    }

    private function tenantId(Request $request): string
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        return (string) (($user->currentTeam ?? null)?->getKey() ?? $user->getAuthIdentifier());
    }

    private function assertTenant(Request $request, InsightSnapshot $record): void
    {
        abort_unless(hash_equals($this->tenantId($request), (string) $record->tenant_id), 404);
    }
}
