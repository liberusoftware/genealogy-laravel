<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Services;

final class AnalyzeDnaMatch
{
    public function __construct(
        private readonly SegmentMatcher $segments,
        private readonly RelationshipEstimator $relationships,
    ) {}

    /** @param array<string, array<int|string, string>> $kitA @param array<string, array<int|string, string>> $kitB @return array<string, mixed> */
    public function analyze(array $kitA, array $kitB): array
    {
        $result = $this->segments->match($kitA, $kitB);

        return [...$result, ...$this->relationships->estimate($result['total_shared_cm'])];
    }
}
