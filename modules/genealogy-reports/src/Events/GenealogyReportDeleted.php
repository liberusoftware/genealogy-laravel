<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Events;

use Liberu\Genealogy\Reports\Models\GenealogyReport;

final class GenealogyReportDeleted
{
    public bool $afterCommit = true;

    public function __construct(public GenealogyReport $report) {}
}
