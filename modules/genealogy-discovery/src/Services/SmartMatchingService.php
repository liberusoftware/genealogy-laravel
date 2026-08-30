<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Services;

use DateTimeImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Liberu\Genealogy\Discovery\Contracts\ExternalRecordProvider;
use Liberu\Genealogy\Discovery\Models\SmartMatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\Person;

final class SmartMatchingService
{
    /** @param iterable<ExternalRecordProvider> $providers */
    public function findMatches(Person $person, iterable $providers, int $minimumConfidence = 60, int $limit = 10): array
    {
        $minimumConfidence = min(max($minimumConfidence, 0), 100);
        $limit = min(max($limit, 1), 100);
        $matches = [];
        $personData = ['id' => $person->getKey(), 'given_name' => $person->given_name, 'family_name' => $person->family_name, 'name' => $person->fullName(), 'birth_date' => $person->birth_date?->toDateString(), 'death_date' => $person->death_date?->toDateString(), 'birth_place' => $person->birth_place];

        foreach ($providers as $provider) {
            if (! $provider->isAvailable()) {
                continue;
            }
            foreach ($provider->search($personData) as $candidate) {
                $confidence = $this->confidence($person, $candidate);
                if ($confidence < $minimumConfidence) {
                    continue;
                }
                $matches[] = ['tree_id' => $candidate['tree_id'] ?? null, 'person_id' => $candidate['id'] ?? $candidate['external_id'] ?? null, 'source' => $provider->key(), 'confidence_score' => $confidence, 'data' => $candidate, 'search_criteria' => $personData];
            }
        }

        usort($matches, fn (array $left, array $right): int => $right['confidence_score'] <=> $left['confidence_score']);

        return array_slice($matches, 0, $limit);
    }

    /** @param list<array<string, mixed>> $matches */
    public function persist(Model $user, Person $person, array $matches): Collection
    {
        $teamId = app(TeamContext::class)->require();

        return collect($matches)->map(fn (array $match): SmartMatch => SmartMatch::query()->create([
            'team_id' => $teamId, 'user_id' => $user->getKey(), 'person_id' => $person->getKey(),
            'external_tree_id' => $match['tree_id'] ?? null, 'external_person_id' => $match['person_id'] ?? null,
            'match_source' => $match['source'], 'match_data' => $match['data'], 'search_criteria' => $match['search_criteria'] ?? null,
            'confidence_score' => $match['confidence_score'], 'status' => 'pending',
        ]));
    }

    /** @param array<string, mixed> $candidate */
    private function confidence(Person $person, array $candidate): int
    {
        $score = 0.0;
        $weight = 0.0;
        $candidateName = trim((string) ($candidate['name'] ?? trim(($candidate['first_name'] ?? '').' '.($candidate['last_name'] ?? ''))));
        if ($candidateName !== '') {
            $score += $this->nameSimilarity($person->fullName(), $candidateName) * 40;
            $weight += 40;
        }
        foreach ([['birth_date', 'birth_date', 30], ['death_date', 'death_date', 20]] as [$personKey, $candidateKey, $factor]) {
            if ($person->getAttribute($personKey) !== null && ! empty($candidate[$candidateKey])) {
                $score += $this->dateSimilarity($person->getAttribute($personKey), $candidate[$candidateKey]) * $factor;
                $weight += $factor;
            }
        }
        if ($person->birth_place !== null && ! empty($candidate['birth_place'])) {
            $score += $this->nameSimilarity($person->birth_place, (string) $candidate['birth_place']) * 10;
            $weight += 10;
        }

        return $weight === 0.0 ? 0 : (int) round(($score / $weight) * 100);
    }

    private function nameSimilarity(string $left, string $right): float
    {
        $left = mb_strtolower(trim($left));
        $right = mb_strtolower(trim($right));
        $length = max(mb_strlen($left), mb_strlen($right));

        return $length === 0 ? 0.0 : max(0.0, 1 - (levenshtein($left, $right) / $length));
    }

    private function dateSimilarity(mixed $left, mixed $right): float
    {
        try {
            $days = abs((new DateTimeImmutable((string) $left))->diff(new DateTimeImmutable((string) $right))->days);
        } catch (\Exception) {
            return 0.0;
        }

        return $days === 0 ? 1.0 : ($days <= 365 ? 0.9 : ($days <= 1825 ? 0.7 : 0.3));
    }
}
