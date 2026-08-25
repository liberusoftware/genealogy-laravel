<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\Dna\Models\DnaSegment;

final class CreateDnaSegment
{
    public function execute(array $attributes): DnaSegment
    {
        $values = Arr::only($attributes, ['match_id', 'chromosome', 'start_position', 'end_position', 'centimorgans', 'snps', 'side', 'metadata']);
        if (! DnaMatch::query()->whereKey($values['match_id'] ?? null)->exists()) {
            throw ValidationException::withMessages(['match_id' => 'The match must belong to the active team.']);
        }

        if (($values['start_position'] ?? 0) >= ($values['end_position'] ?? 0)) {
            throw ValidationException::withMessages(['end_position' => 'The segment end must be after its start.']);
        }

        return DnaSegment::query()->create($values);
    }
}
