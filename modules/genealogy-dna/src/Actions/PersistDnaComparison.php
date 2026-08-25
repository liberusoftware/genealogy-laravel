<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Events\DnaMatchesPersisted;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\Dna\Services\CompareDnaKits;

final class PersistDnaComparison
{
    public function __construct(
        private readonly CompareDnaKits $compare,
        private readonly CreateDnaMatch $createMatch,
        private readonly CreateDnaSegment $createSegment,
    ) {}

    /** @return array<string, mixed> */
    public function execute(DnaKit $kitA, DnaKit $kitB): array
    {
        try {
            $result = $this->compare->execute($kitA, $kitB);
        } catch (InvalidArgumentException $exception) {
            if (! str_contains($exception->getMessage(), 'readable genotype')) {
                throw $exception;
            }

            return [
                'comparison_performed' => false,
                'skipped_reason' => 'unreadable_genotype_data',
                'persisted_match_ids' => [],
            ];
        }
        $segments = is_array($result['shared_segments'] ?? null) ? $result['shared_segments'] : [];

        $newMatchIds = [];
        $matches = DB::transaction(function () use ($kitA, $kitB, $result, $segments, &$newMatchIds): array {
            [$matchA, $createdA] = $this->persistMatch($kitA, $kitB, $result, $segments);
            [$matchB, $createdB] = $this->persistMatch($kitB, $kitA, $result, $segments);
            if ($createdA) {
                $newMatchIds[] = (string) $matchA->getKey();
            }
            if ($createdB) {
                $newMatchIds[] = (string) $matchB->getKey();
            }

            return [
                $matchA, $matchB,
            ];
        });

        event(new DnaMatchesPersisted($kitA, $kitB, array_map(static fn (DnaMatch $match): string => (string) $match->getKey(), $matches), $newMatchIds));

        return [...$result, 'persisted_match_ids' => array_map(static fn (DnaMatch $match): string => (string) $match->getKey(), $matches)];
    }

    /** @param array<string, mixed> $result @param list<array<string, mixed>> $segments */
    /** @return array{0: DnaMatch, 1: bool} */
    private function persistMatch(DnaKit $kit, DnaKit $otherKit, array $result, array $segments): array
    {
        $externalId = 'kit:'.$otherKit->getKey();
        $match = DnaMatch::query()->where('kit_id', $kit->getKey())->where('external_id', $externalId)->first();
        $created = false;
        if ($match === null) {
            $created = true;
            $match = $this->createMatch->execute([
                'kit_id' => $kit->getKey(),
                'external_id' => $externalId,
                'display_name' => $otherKit->name,
                'predicted_relationship' => $result['predicted_relationship'] ?? null,
                'confidence' => isset($result['match_quality_score']) ? (int) round((float) $result['match_quality_score']) : null,
                'total_cm' => $result['total_shared_cm'] ?? 0,
                'shared_segments' => $result['shared_segments_count'] ?? 0,
                'status' => 'active',
                'is_private' => true,
                'metadata' => ['source' => 'stored-kit-comparison', 'compared_kit_id' => (string) $otherKit->getKey()],
            ]);
        }

        if ($match->segments()->exists()) {
            return [$match, $created];
        }

        foreach ($segments as $segment) {
            $this->createSegment->execute([
                'match_id' => $match->getKey(),
                'chromosome' => (int) ($segment['chromosome'] ?? 0),
                'start_position' => (int) ($segment['start'] ?? 0),
                'end_position' => (int) ($segment['end'] ?? 0),
                'centimorgans' => $segment['cm'] ?? null,
                'snps' => $segment['snps'] ?? null,
                'metadata' => ['source' => 'stored-kit-comparison'],
            ]);
        }

        return [$match->refresh(), $created];
    }
}
