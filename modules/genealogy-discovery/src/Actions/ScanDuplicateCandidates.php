<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Actions;

use InvalidArgumentException;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Liberu\Genealogy\Discovery\Queries\DuplicateCandidates;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class ScanDuplicateCandidates
{
    public function __construct(
        private readonly DuplicateCandidates $candidates,
        private readonly CreateDiscoveryMatch $create,
    ) {}

    /** @return array{scanned: int, created: int, existing: int, matches: list<string>} */
    public function execute(float $threshold = 0.7, int $limit = 100): array
    {
        if ($threshold < 0 || $threshold > 1) {
            throw new InvalidArgumentException('The duplicate threshold must be between 0 and 1.');
        }
        if ($limit < 1 || $limit > 1000) {
            throw new InvalidArgumentException('The duplicate scan limit must be between 1 and 1000.');
        }

        app(TeamContext::class)->require();
        $candidates = $this->candidates->execute($limit, (int) ceil($threshold * 100));
        $created = 0;
        $existing = 0;
        $matchIds = [];

        foreach ($candidates as $candidate) {
            $query = DiscoveryMatch::query()
                ->where('kind', 'duplicate')
                ->where('subject_id', $candidate['person_id'])
                ->where('related_id', $candidate['candidate_id']);
            $match = $query->first();

            if ($match !== null) {
                $existing++;
                $matchIds[] = (string) $match->getKey();

                continue;
            }

            $match = $this->create->execute([
                'kind' => 'duplicate',
                'name' => 'Possible duplicate person',
                'subject_id' => $candidate['person_id'],
                'related_id' => $candidate['candidate_id'],
                'confidence' => $candidate['confidence'],
                'rationale' => implode(', ', $candidate['reasons']),
                'source_type' => 'duplicate-scan',
                'status' => 'active',
                'detected_at' => now(),
                'metadata' => ['threshold' => $threshold],
            ]);
            $created++;
            $matchIds[] = (string) $match->getKey();
        }

        return ['scanned' => count($candidates), 'created' => $created, 'existing' => $existing, 'matches' => $matchIds];
    }
}
