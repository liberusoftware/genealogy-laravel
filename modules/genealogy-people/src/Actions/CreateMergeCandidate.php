<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\MergeCandidate;
use Liberu\Genealogy\People\Support\PersonReference;

final class CreateMergeCandidate
{
    public function execute(array $attributes): MergeCandidate
    {
        $values = Arr::only($attributes, ['person_id', 'candidate_person_id', 'status', 'score', 'reason', 'metadata', 'reviewed_at']);
        $personId = app(PersonReference::class)->require($values['person_id'] ?? null);
        $candidateId = app(PersonReference::class)->require($values['candidate_person_id'] ?? null);
        if ($personId === $candidateId) {
            throw new InvalidArgumentException('A merge candidate must contain two different people.');
        }
        $values['person_id'] = $personId;
        $values['candidate_person_id'] = $candidateId;
        $values['team_id'] = app(TeamContext::class)->require();

        return MergeCandidate::query()->create($values);
    }
}
