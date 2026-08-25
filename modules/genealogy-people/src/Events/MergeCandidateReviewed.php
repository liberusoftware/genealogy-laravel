<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Events;

use Liberu\Genealogy\People\Models\MergeCandidate;

final class MergeCandidateReviewed
{
    public bool $afterCommit = true;

    public function __construct(public MergeCandidate $candidate) {}
}
