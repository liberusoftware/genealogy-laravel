<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaKit;

final class CreateDnaKit
{
    public function execute(array $attributes): DnaKit
    {
        $values = Arr::only($attributes, ['name', 'provider', 'external_id', 'person_id', 'test_type', 'consent_status', 'consented_at', 'revoked_at', 'revocation_reason', 'status', 'metadata']);
        if (isset($values['consent_status']) && ! in_array($values['consent_status'], DnaKit::CONSENT_STATUSES, true)) {
            throw ValidationException::withMessages(['consent_status' => 'The selected consent status is invalid.']);
        }

        return DnaKit::query()->create($values);
    }
}
