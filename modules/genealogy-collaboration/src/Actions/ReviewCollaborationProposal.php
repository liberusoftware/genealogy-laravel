<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Events\CollaborationProposalReviewed;
use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class ReviewCollaborationProposal
{
    public function execute(CollaborationProposal $proposal, string $status, string|int|null $reviewerId = null): CollaborationProposal
    {
        if ((string) $proposal->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The proposal must belong to the active team.');
        }
        if (! in_array($status, ['in_review', 'approved', 'rejected'], true)) {
            throw new InvalidArgumentException('The proposal review status is invalid.');
        }

        DB::transaction(function () use ($proposal, $status, $reviewerId): void {
            $proposal->update([
                'status' => $status,
                'reviewer_id' => $reviewerId ?? (function_exists('auth') && auth()->check() ? auth()->id() : null),
                'reviewed_at' => $status === 'in_review' ? null : now(),
            ]);
        });
        $proposal = $proposal->refresh();
        event(new CollaborationProposalReviewed($proposal));

        return $proposal;
    }
}
