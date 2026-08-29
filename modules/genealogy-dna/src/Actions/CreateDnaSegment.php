<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\Dna\Models\DnaSegment;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class CreateDnaSegment
{
    public function execute(array $attributes): DnaSegment
    {
        $teamId = app(TeamContext::class)->require();
        $values = Arr::only($attributes, ['match_id', 'chromosome', 'start_position', 'end_position', 'centimorgans', 'snps', 'side', 'metadata']);
        if (! DnaMatch::query()->whereKey($values['match_id'] ?? null)->exists()) {
            throw ValidationException::withMessages(['match_id' => 'The match must belong to the active team.']);
        }

        if (($values['chromosome'] ?? 0) < 1 || ($values['chromosome'] ?? 0) > 99) {
            throw ValidationException::withMessages(['chromosome' => 'The chromosome must be between 1 and 99.']);
        }

        if (($values['start_position'] ?? 0) >= ($values['end_position'] ?? 0)) {
            throw ValidationException::withMessages(['end_position' => 'The segment end must be after its start.']);
        }

        $values['team_id'] = $teamId;

        return DnaSegment::query()->create($values);
    }
}
