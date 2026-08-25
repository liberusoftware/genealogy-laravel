<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Dna\Actions\CreateDnaKit;
use Liberu\Genealogy\Dna\Actions\GrantDnaConsent;
use Liberu\Genealogy\Dna\Actions\RevokeDnaKit;
use Liberu\Genealogy\Dna\Models\DnaKit;

final class DnaKitController
{
    public function index(Request $request): JsonResponse
    {
        $kits = DnaKit::query()->latest()->paginate(min(max($request->integer('page[size]', 25), 1), 100));

        return response()->json(['data' => $kits->through(fn (DnaKit $kit): array => $this->resource($kit)), 'meta' => ['current_page' => $kits->currentPage(), 'per_page' => $kits->perPage(), 'total' => $kits->total()]]);
    }

    public function store(Request $request, CreateDnaKit $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'], 'provider' => ['nullable', 'string', 'max:100'], 'external_id' => ['nullable', 'string', 'max:255'],
            'person_id' => ['nullable', 'uuid'], 'test_type' => ['nullable', 'string', 'max:100'], 'consent_status' => ['sometimes', 'in:'.implode(',', DnaKit::CONSENT_STATUSES)],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function show(DnaKit $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, DnaKit $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'], 'provider' => ['nullable', 'string', 'max:100'], 'external_id' => ['nullable', 'string', 'max:255'],
            'person_id' => ['nullable', 'uuid'], 'test_type' => ['nullable', 'string', 'max:100'], 'consent_status' => ['sometimes', 'in:'.implode(',', DnaKit::CONSENT_STATUSES)],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record->refresh())]);
    }

    public function destroy(DnaKit $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }

    public function consent(Request $request, DnaKit $kit, GrantDnaConsent $grant): JsonResponse
    {
        $values = $request->validate(['scope' => ['required', 'string', 'max:100'], 'policy_version' => ['nullable', 'string', 'max:100']]);
        $consent = $grant->execute($kit, $values['scope'], $values['policy_version'] ?? null);

        return response()->json(['data' => ['id' => $consent->getKey(), 'kit_id' => $consent->kit_id, 'scope' => $consent->scope, 'granted' => $consent->granted, 'granted_at' => $consent->granted_at?->toISOString()]], 201);
    }

    public function revoke(Request $request, DnaKit $kit, RevokeDnaKit $revoke): JsonResponse
    {
        $values = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        return response()->json(['data' => $this->resource($revoke->execute($kit, $values['reason']))]);
    }

    /** @return array<string, mixed> */
    private function resource(DnaKit $kit): array
    {
        return ['id' => $kit->getKey(), 'type' => 'genealogy-dna-kit', 'attributes' => ['name' => $kit->name, 'provider' => $kit->provider, 'external_id' => $kit->external_id, 'person_id' => $kit->person_id, 'test_type' => $kit->test_type, 'consent_status' => $kit->consent_status, 'consented_at' => $kit->consented_at?->toISOString(), 'revoked_at' => $kit->revoked_at?->toISOString(), 'revocation_reason' => $kit->revocation_reason, 'status' => $kit->status, 'metadata' => $kit->metadata]];
    }
}
