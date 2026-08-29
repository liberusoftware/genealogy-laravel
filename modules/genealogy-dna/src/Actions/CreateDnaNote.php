<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\Dna\Models\DnaNote;

final class CreateDnaNote
{
    public function execute(array $attributes): DnaNote
    {
        $values = Arr::only($attributes, ['noteable_type', 'noteable_id', 'body']);
        if (! in_array($values['noteable_type'] ?? null, [DnaKit::class, DnaMatch::class], true)) {
            throw ValidationException::withMessages(['noteable_type' => 'The note target is invalid.']);
        }
        if (trim((string) ($values['body'] ?? '')) === '') {
            throw ValidationException::withMessages(['body' => 'A DNA note cannot be empty.']);
        }
        $model = $values['noteable_type'];
        if (! $model::query()->whereKey($values['noteable_id'] ?? null)->exists()) {
            throw ValidationException::withMessages(['noteable_id' => 'The note target must belong to the active team.']);
        }

        return DnaNote::query()->create($values);
    }
}
