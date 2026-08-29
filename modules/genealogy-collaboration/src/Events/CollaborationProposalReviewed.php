<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;

final class CollaborationProposalReviewed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly CollaborationProposal $proposal) {}
}
