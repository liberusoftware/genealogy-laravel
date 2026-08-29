<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Services;

use InvalidArgumentException;

final class TriangulateDna
{
    /** @param list<array{id: string|int, segments: list<array{chromosome: string|int, start: int, end: int}>}> $matches @return list<array{match_ids: list<string|int>, chromosome: string, start: int, end: int, centimorgans: float}> */
    public function execute(array $matches, float $minimumSharedCm = 20.0): array
    {
        if ($minimumSharedCm < 0) {
            throw new InvalidArgumentException('The minimum shared cM must not be negative.');
        }
        $matches = array_values($matches);
        if (count($matches) < 3) {
            return [];
        }

        $groups = [];
        $count = count($matches);
        for ($first = 0; $first < $count - 2; $first++) {
            for ($second = $first + 1; $second < $count - 1; $second++) {
                for ($third = $second + 1; $third < $count; $third++) {
                    foreach ($this->commonSegments($matches[$first]['segments'], $matches[$second]['segments'], $matches[$third]['segments']) as $segment) {
                        if ($segment['centimorgans'] >= $minimumSharedCm) {
                            $groups[] = ['match_ids' => [$matches[$first]['id'], $matches[$second]['id'], $matches[$third]['id']], ...$segment];
                        }
                    }
                }
            }
        }

        return $groups;
    }

    /** @param list<array{chromosome: string|int, start: int, end: int}> $first @param list<array{chromosome: string|int, start: int, end: int}> $second @param list<array{chromosome: string|int, start: int, end: int}> $third @return list<array{chromosome: string, start: int, end: int, centimorgans: float}> */
    private function commonSegments(array $first, array $second, array $third): array
    {
        $groups = [];
        foreach ($first as $left) {
            foreach ($second as $middle) {
                foreach ($third as $right) {
                    if ((string) $left['chromosome'] !== (string) $middle['chromosome'] || (string) $left['chromosome'] !== (string) $right['chromosome']) {
                        continue;
                    }
                    $start = max($left['start'], $middle['start'], $right['start']);
                    $end = min($left['end'], $middle['end'], $right['end']);
                    if ($end <= $start) {
                        continue;
                    }
                    $groups[] = ['chromosome' => (string) $left['chromosome'], 'start' => $start, 'end' => $end, 'centimorgans' => round(($end - $start) / 1_000_000, 2)];
                }
            }
        }

        return $groups;
    }
}
