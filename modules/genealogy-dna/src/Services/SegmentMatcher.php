<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Services;

final class SegmentMatcher
{
    public const MIN_SNPS = 500;

    public const MISMATCH_TOLERANCE = 3;

    public const CM_PER_MB = 1.0;

    public const MIN_CM = 7.0;

    /** @param array<string, array<int|string, string>> $kitA @param array<string, array<int|string, string>> $kitB @return array{total_shared_cm: float, largest_cm_segment: float, shared_segments_count: int, total_matching_snps: int, shared_segments: list<array{chromosome: string, start: int, end: int, cm: float, snps: int}>} */
    public function match(array $kitA, array $kitB): array
    {
        $segments = [];
        foreach (range(1, 22) as $chromosome) {
            $chr = (string) $chromosome;
            if (! isset($kitA[$chr], $kitB[$chr])) {
                continue;
            }
            $positions = array_keys(array_intersect_key($kitA[$chr], $kitB[$chr]));
            usort($positions, static fn (int|string $a, int|string $b): int => (int) $a <=> (int) $b);
            $runStart = null;
            $runEnd = null;
            $runSnps = 0;
            $mismatches = 0;
            foreach ($positions as $position) {
                $position = (int) $position;
                if ($this->isHalfIdentical($kitA[$chr][$position] ?? '', $kitB[$chr][$position] ?? '')) {
                    $runStart ??= $position;
                    $runEnd = $position;
                    $runSnps++;
                    $mismatches = 0;

                    continue;
                }
                if ($runStart !== null && ++$mismatches >= self::MISMATCH_TOLERANCE) {
                    $segment = $this->buildSegment($chr, $runStart, $runEnd, $runSnps);
                    if ($segment !== null) {
                        $segments[] = $segment;
                    }
                    $runStart = $runEnd = null;
                    $runSnps = $mismatches = 0;
                }
            }
            $segment = $this->buildSegment($chr, $runStart, $runEnd, $runSnps);
            if ($segment !== null) {
                $segments[] = $segment;
            }
        }
        $totalCm = array_sum(array_column($segments, 'cm'));
        $largestCm = $segments === [] ? 0.0 : max(array_column($segments, 'cm'));

        return [
            'total_shared_cm' => round($totalCm, 2),
            'largest_cm_segment' => round((float) $largestCm, 2),
            'shared_segments_count' => count($segments),
            'total_matching_snps' => array_sum(array_column($segments, 'snps')),
            'shared_segments' => $segments,
        ];
    }

    private function isHalfIdentical(string $left, string $right): bool
    {
        if (strlen($left) < 2 || strlen($right) < 2) {
            return false;
        }

        return $left[0] === $right[0] || $left[0] === $right[1] || $left[1] === $right[0] || $left[1] === $right[1];
    }

    /** @return array{chromosome: string, start: int, end: int, cm: float, snps: int}|null */
    private function buildSegment(string $chromosome, ?int $start, ?int $end, int $snps): ?array
    {
        if ($start === null || $end === null || $snps < self::MIN_SNPS) {
            return null;
        }
        $cm = (($end - $start) / 1_000_000) * self::CM_PER_MB;
        if ($cm < self::MIN_CM) {
            return null;
        }

        return ['chromosome' => $chromosome, 'start' => $start, 'end' => $end, 'cm' => round($cm, 2), 'snps' => $snps];
    }
}
