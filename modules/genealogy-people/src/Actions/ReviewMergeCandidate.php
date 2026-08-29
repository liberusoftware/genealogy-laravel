<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Events\MergeCandidateReviewed;
use Liberu\Genealogy\People\Models\MergeCandidate;

final class ReviewMergeCandidate
{
    public function __construct(private readonly ?MergePersons $mergePersons = null) {}

    public function execute(MergeCandidate $candidate, string $status, ?string $reason = null): MergeCandidate
    {
        if ((string) $candidate->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The merge candidate must belong to the active team.');
        }
        if (! in_array($status, ['accepted', 'rejected'], true)) {
            throw new InvalidArgumentException('A merge candidate review must be accepted or rejected.');
        }

        DB::transaction(function () use ($candidate, $status, $reason): void {
            $candidate->forceFill([
                'status' => $status,
                'reason' => $reason ?? $candidate->reason,
                'reviewed_at' => now(),
            ])->save();
        });
        if ($status === 'accepted') {
            ($this->mergePersons ?? new MergePersons())->execute(
                $candidate->person()->firstOrFail(),
                $candidate->candidatePerson()->firstOrFail(),
            );
        }
        event(new MergeCandidateReviewed($candidate->refresh()));

        return $candidate;
    }
}
