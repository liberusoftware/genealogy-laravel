<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Events;

use Liberu\Genealogy\Research\Models\ResearchProject;

final class ResearchProjectDeleted
{
    public bool $afterCommit = true;

    public function __construct(public ResearchProject $project) {}
}
