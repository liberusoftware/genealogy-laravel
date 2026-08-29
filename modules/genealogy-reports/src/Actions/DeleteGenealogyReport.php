<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Reports\Events\GenealogyReportDeleted;
use Liberu\Genealogy\Reports\Models\GenealogyReport;

final class DeleteGenealogyReport
{
    public function execute(GenealogyReport $report): void
    {
        if ((string) $report->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The report must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $report->delete());
        event(new GenealogyReportDeleted($report));
    }
}
