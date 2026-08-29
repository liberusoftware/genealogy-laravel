<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\Dna\Actions\CreateDnaKit;
use Liberu\Genealogy\Dna\Actions\DeleteDnaKit;
use Liberu\Genealogy\Dna\Actions\GrantDnaConsent;
use Liberu\Genealogy\Dna\Actions\ImportDnaKit;
use Liberu\Genealogy\Dna\Actions\RevokeDnaKit;
use Liberu\Genealogy\Dna\Actions\UpdateDnaKit;
use Liberu\Genealogy\Dna\Models\DnaConsent;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\Dna\Services\DnaFileValidator;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DnaKitController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
        ]);
        $kits = DnaKit::query()->latest()->paginate($values['page']['size'] ?? 25);

        return response()->json(['data' => $kits->getCollection()->map(fn (DnaKit $kit): array => $this->resource($kit))->values()->all(), 'meta' => ['current_page' => $kits->currentPage(), 'per_page' => $kits->perPage(), 'total' => $kits->total()]]);
    }

    public function store(Request $request, CreateDnaKit $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'], 'provider' => ['nullable', 'string', 'max:100'], 'provider_id' => ['nullable', 'uuid', Rule::exists('genealogy_dna_providers', 'id')->where('team_id', app(TeamContext::class)->require())], 'external_id' => ['nullable', 'string', 'max:255'],
            'person_id' => ['nullable', 'uuid'], 'test_type' => ['nullable', 'string', 'max:100'], 'consent_status' => ['sometimes', 'in:'.implode(',', DnaKit::CONSENT_STATUSES)],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function validateFile(Request $request, DnaFileValidator $validator): JsonResponse
    {
        $content = $request->validate(['content' => ['required', 'string', 'max:104857600']])['content'];

        return response()->json(['data' => $validator->validate($content)]);
    }

    public function import(Request $request, ImportDnaKit $import): JsonResponse
    {
        $values = $request->validate([
            'content' => ['required', 'string', 'max:104857600'],
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:100'],
            'provider_id' => ['nullable', 'uuid', Rule::exists('genealogy_dna_providers', 'id')->where('team_id', app(TeamContext::class)->require())],
            'external_id' => ['nullable', 'string', 'max:255'],
            'person_id' => ['nullable', 'uuid'],
            'test_type' => ['nullable', 'string', 'max:100'],
            'consent_status' => ['sometimes', 'in:'.implode(',', DnaKit::CONSENT_STATUSES)],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]);
        $content = $values['content'];
        unset($values['content']);
        $record = $import->execute($content, $values);

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function show(DnaKit $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, DnaKit $record, UpdateDnaKit $update): JsonResponse
    {
        $values = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'], 'provider' => ['nullable', 'string', 'max:100'], 'provider_id' => ['nullable', 'uuid', Rule::exists('genealogy_dna_providers', 'id')->where('team_id', app(TeamContext::class)->require())], 'external_id' => ['nullable', 'string', 'max:255'],
            'person_id' => ['nullable', 'uuid'], 'test_type' => ['nullable', 'string', 'max:100'], 'consent_status' => ['sometimes', 'in:'.implode(',', DnaKit::CONSENT_STATUSES)],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]);

        return response()->json(['data' => $this->resource($update->execute($record, $values))]);
    }

    public function destroy(DnaKit $record, DeleteDnaKit $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    public function consent(Request $request, DnaKit $kit, GrantDnaConsent $grant): JsonResponse
    {
        $values = $request->validate(['scope' => ['required', 'string', 'max:100'], 'policy_version' => ['nullable', 'string', 'max:100']]);
        $consent = $grant->execute($kit, $values['scope'], $values['policy_version'] ?? null);

        return response()->json(['data' => ['id' => $consent->getKey(), 'kit_id' => $consent->kit_id, 'scope' => $consent->scope, 'granted' => $consent->granted, 'granted_at' => $consent->granted_at?->toISOString()]], 201);
    }

    public function consents(DnaKit $kit): JsonResponse
    {
        $records = DnaConsent::query()->where('kit_id', $kit->getKey())->latest()->get();

        return response()->json(['data' => $records->map(fn (DnaConsent $consent): array => $this->consentResource($consent))->all()]);
    }

    public function revoke(Request $request, DnaKit $kit, RevokeDnaKit $revoke): JsonResponse
    {
        $values = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        return response()->json(['data' => $this->resource($revoke->execute($kit, $values['reason']))]);
    }

    /** @return array<string, mixed> */
    private function resource(DnaKit $kit): array
    {
        return ['id' => $kit->getKey(), 'type' => 'genealogy-dna-kit', 'attributes' => ['name' => $kit->name, 'provider' => $kit->provider, 'provider_id' => $kit->provider_id, 'external_id' => $kit->external_id, 'person_id' => $kit->person_id, 'test_type' => $kit->test_type, 'consent_status' => $kit->consent_status, 'consented_at' => $kit->consented_at?->toISOString(), 'revoked_at' => $kit->revoked_at?->toISOString(), 'revocation_reason' => $kit->revocation_reason, 'status' => $kit->status, 'metadata' => $kit->metadata, 'file_format' => $kit->file_format, 'snp_count' => $kit->snp_count, 'has_file' => $kit->file_path !== null]];
    }

    /** @return array<string, mixed> */
    private function consentResource(DnaConsent $consent): array
    {
        return ['id' => $consent->getKey(), 'type' => 'genealogy-dna-consent', 'attributes' => [
            'kit_id' => $consent->kit_id,
            'scope' => $consent->scope,
            'granted' => $consent->granted,
            'policy_version' => $consent->policy_version,
            'granted_at' => $consent->granted_at?->toISOString(),
            'revoked_at' => $consent->revoked_at?->toISOString(),
            'revocation_reason' => $consent->revocation_reason,
            'metadata' => $consent->metadata,
        ]];
    }
}
