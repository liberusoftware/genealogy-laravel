<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateDnaKit
{
    public function execute(DnaKit $kit, array $attributes): DnaKit
    {
        if ((string) $kit->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA kit must belong to the active team.');
        }
        $values = Arr::only($attributes, ['name', 'provider', 'external_id', 'person_id', 'test_type', 'consent_status', 'status', 'metadata']);
        if (isset($values['consent_status']) && ! in_array($values['consent_status'], DnaKit::CONSENT_STATUSES, true)) {
            throw ValidationException::withMessages(['consent_status' => 'The selected consent status is invalid.']);
        }
        if (array_key_exists('name', $values) && trim((string) $values['name']) === '') {
            throw ValidationException::withMessages(['name' => 'A DNA kit name is required.']);
        }

        DB::transaction(fn (): bool => $kit->update($values));

        return $kit->refresh();
    }
}
