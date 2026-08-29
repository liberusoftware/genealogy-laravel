<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaGroup;

final class CreateDnaGroup
{
    public function execute(array $attributes): DnaGroup
    {
        $values = Arr::only($attributes, ['name', 'description', 'status', 'metadata']);
        $this->validate($values);
        $values['status'] ??= 'active';

        return DnaGroup::query()->create($values);
    }

    /** @param array<string, mixed> $values */
    public function validate(array $values): void
    {
        if (trim((string) ($values['name'] ?? '')) === '') {
            throw ValidationException::withMessages(['name' => 'A DNA group name is required.']);
        }
        if (isset($values['status']) && ! in_array($values['status'], DnaGroup::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'The DNA group status is invalid.']);
        }
    }
}
