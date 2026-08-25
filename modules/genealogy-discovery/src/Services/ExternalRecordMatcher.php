<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Services;

use Liberu\Genealogy\Discovery\Contracts\ExternalRecordProvider;

final class ExternalRecordMatcher
{
    /** @var array<string, float> */
    private const DEFAULT_WEIGHTS = ['first_name' => 1.0, 'last_name' => 1.0, 'birth_year' => 0.8, 'birth_place' => 0.6, 'parents' => 0.9];

    public function __construct(private readonly ?ExternalRecordProvider $provider = null) {}

    /** @return array{available: bool, provider: ?string, candidates: list<array{candidate: array<string, mixed>, score: float}>, error: ?string} */
    public function execute(array $person, array $weights = []): array
    {
        if ($this->provider === null || ! $this->provider->isAvailable()) {
            return ['available' => false, 'provider' => null, 'candidates' => [], 'error' => 'External record discovery is not configured.'];
        }

        return ['available' => true, 'provider' => $this->provider->key(), 'candidates' => $this->scoreCandidates($person, $this->provider->search($person), $weights), 'error' => null];
    }

    /** @param list<array<string, mixed>> $candidates @return list<array{candidate: array<string, mixed>, score: float}> */
    public function scoreCandidates(array $person, array $candidates, array $weights = []): array
    {
        $weights = array_replace(self::DEFAULT_WEIGHTS, array_intersect_key($weights, self::DEFAULT_WEIGHTS));
        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0) {
            return array_map(static fn (array $candidate): array => ['candidate' => $candidate, 'score' => 0.0], $candidates);
        }

        $results = array_map(function (array $candidate) use ($person, $weights, $totalWeight): array {
            $score = 0.0;
            $score += $weights['first_name'] * $this->similarity($person['first_name'] ?? $person['given_name'] ?? '', $candidate['first_name'] ?? '');
            $score += $weights['last_name'] * $this->similarity($person['last_name'] ?? $person['family_name'] ?? '', $candidate['last_name'] ?? '');
            $score += $weights['birth_year'] * $this->yearSimilarity($person['birth_year'] ?? null, $candidate['birth_year'] ?? null);
            $score += $weights['birth_place'] * $this->similarity($person['birth_place'] ?? '', $candidate['birth_place'] ?? '');
            $score += $weights['parents'] * $this->similarity($person['last_name'] ?? $person['family_name'] ?? '', $candidate['last_name'] ?? '');

            return ['candidate' => $candidate, 'score' => min(1.0, round($score / $totalWeight, 4))];
        }, $candidates);
        usort($results, static fn (array $left, array $right): int => $right['score'] <=> $left['score']);

        return $results;
    }

    private function similarity(mixed $left, mixed $right): float
    {
        $left = mb_strtolower(trim((string) $left));
        $right = mb_strtolower(trim((string) $right));
        if ($left === '' || $right === '') {
            return 0.0;
        }
        similar_text($left, $right, $percent);

        return $percent / 100;
    }

    private function yearSimilarity(mixed $left, mixed $right): float
    {
        if (! is_numeric($left) || ! is_numeric($right)) {
            return 0.0;
        }
        $difference = abs((int) $left - (int) $right);

        return $difference === 0 ? 1.0 : ($difference <= 2 ? 0.7 : ($difference <= 5 ? 0.4 : 0.0));
    }
}
