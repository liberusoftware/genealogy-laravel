<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Queries;

use Liberu\Genealogy\People\Models\Person;

final class DuplicateCandidates
{
    /** @return list<array{person_id: string, candidate_id: string, confidence: int, reasons: list<string>}> */
    public function execute(int $limit = 100): array
    {
        $people = Person::query()->orderBy('family_name')->orderBy('given_name')->get();
        $results = [];

        foreach ($people as $index => $person) {
            for ($candidateIndex = $index + 1; $candidateIndex < $people->count(); $candidateIndex++) {
                $candidate = $people[$candidateIndex];
                $reasons = [];
                $confidence = 0;
                if ($this->normalise($person->family_name) !== '' && $this->normalise($person->family_name) === $this->normalise($candidate->family_name)) {
                    $confidence += 35;
                    $reasons[] = 'matching_family_name';
                }
                if ($this->normalise($person->given_name) !== '' && $this->normalise($person->given_name) === $this->normalise($candidate->given_name)) {
                    $confidence += 35;
                    $reasons[] = 'matching_given_name';
                }
                if ($person->birth_date !== null && $candidate->birth_date !== null && $person->birth_date->isSameDay($candidate->birth_date)) {
                    $confidence += 30;
                    $reasons[] = 'matching_birth_date';
                }
                if ($confidence >= 70) {
                    $results[] = ['person_id' => (string) $person->getKey(), 'candidate_id' => (string) $candidate->getKey(), 'confidence' => $confidence, 'reasons' => $reasons];
                }
                if (count($results) >= $limit) {
                    return $results;
                }
            }
        }

        return $results;
    }

    private function normalise(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }
}
