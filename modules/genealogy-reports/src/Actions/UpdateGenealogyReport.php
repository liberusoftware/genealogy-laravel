<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Reports\Events\GenealogyReportUpdated;
use Liberu\Genealogy\Reports\Models\GenealogyReport;

final class UpdateGenealogyReport
{
    /** @param array<string, mixed> $attributes */
    public function execute(GenealogyReport $report, array $attributes): GenealogyReport
    {
        if ((string) $report->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The report must belong to the active team.');
        }
        $values = Arr::only($attributes, ['name', 'type', 'status', 'metadata']);
        (new CreateGenealogyReport())->validate(array_merge($report->toArray(), $values));
        $report->getConnection()->transaction(function () use ($report, $values): void {
            $report->update($values);
        });

        $report = $report->refresh();
        if (app()->bound('events')) {
            event(new GenealogyReportUpdated($report));
        }

        return $report;
    }
}
