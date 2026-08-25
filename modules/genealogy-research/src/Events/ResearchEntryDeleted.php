<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Events;

use Liberu\Genealogy\Research\Models\ResearchEntry;

final class ResearchEntryDeleted
{
    public bool $afterCommit = true;

    public function __construct(public ResearchEntry $entry) {}
}
