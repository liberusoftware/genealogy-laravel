<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\Dna\Models\DnaProvider;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\Person;

final class CreateDnaKit
{
    public function execute(array $attributes): DnaKit
    {
        $teamId = app(TeamContext::class)->require();
        $values = Arr::only($attributes, ['name', 'provider', 'provider_id', 'external_id', 'person_id', 'test_type', 'consent_status', 'consented_at', 'revoked_at', 'revocation_reason', 'status', 'metadata', 'file_path', 'file_hash', 'file_format', 'snp_count']);
        $values['name'] = trim((string) ($values['name'] ?? ''));
        if ($values['name'] === '') {
            throw ValidationException::withMessages(['name' => 'A DNA kit name is required.']);
        }
        if (isset($values['consent_status']) && ! in_array($values['consent_status'], DnaKit::CONSENT_STATUSES, true)) {
            throw ValidationException::withMessages(['consent_status' => 'The selected consent status is invalid.']);
        }
        if (isset($values['status']) && ! in_array($values['status'], DnaKit::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'The selected DNA kit status is invalid.']);
        }
        $this->assertProvider($values['provider_id'] ?? null);
        $this->assertPerson($values['person_id'] ?? null, $teamId);
        $values['team_id'] = $teamId;

        return DnaKit::query()->create($values);
    }

    private function assertProvider(?string $providerId): void
    {
        if ($providerId === null) {
            return;
        }
        $teamId = app(TeamContext::class)->require();
        if (! DnaProvider::query()->whereKey($providerId)->where('team_id', $teamId)->exists()) {
            throw ValidationException::withMessages(['provider_id' => 'The selected DNA provider is not available in the active team.']);
        }
    }

    private function assertPerson(?string $personId, string $teamId): void
    {
        if ($personId !== null && ! Person::query()->whereKey($personId)->where('team_id', $teamId)->exists()) {
            throw ValidationException::withMessages(['person_id' => 'The selected DNA person is not available in the active team.']);
        }
    }
}
