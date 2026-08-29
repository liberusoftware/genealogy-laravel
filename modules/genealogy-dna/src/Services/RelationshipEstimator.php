<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Services;

final class RelationshipEstimator
{
    public const NO_MATCH_LABEL = 'Unrelated / No significant match';

    /** @var list<array{0: float, 1: string, 2: string}> */
    private const BANDS = [
        [4000.0, 'Identical Twin / Same Person', 'very_high'],
        [3400.0, 'Parent/Child', 'very_high'],
        [2300.0, 'Full Sibling', 'very_high'],
        [1300.0, 'Grandparent/Aunt/Uncle/Half-sibling', 'high'],
        [575.0, '1st Cousin', 'high'],
        [200.0, '1st Cousin Once Removed / 2nd Cousin', 'medium'],
        [75.0, '2nd-3rd Cousin', 'medium'],
        [20.0, 'Distant Cousin', 'low'],
    ];

    /** @return list<string> */
    public static function labels(): array
    {
        return [self::NO_MATCH_LABEL, ...array_column(self::BANDS, 1)];
    }

    /** @return array{predicted_relationship: string, confidence_level: string, match_quality_score: float} */
    public function estimate(float $totalSharedCm): array
    {
        $cm = max(0.0, $totalSharedCm);
        $relationship = self::NO_MATCH_LABEL;
        $confidence = 'low';
        foreach (self::BANDS as [$minimum, $label, $level]) {
            if ($cm >= $minimum) {
                $relationship = $label;
                $confidence = $level;
                break;
            }
        }

        return [
            'predicted_relationship' => $relationship,
            'confidence_level' => $confidence,
            'match_quality_score' => round(min(100.0, $cm / 6800.0 * 100.0), 2),
        ];
    }
}
