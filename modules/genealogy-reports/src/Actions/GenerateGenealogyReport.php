<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Reports\Events\GenealogyReportUpdated;
use Liberu\Genealogy\Reports\Models\GenealogyReport;
use Liberu\Genealogy\Reports\Queries\BuildGenealogyReport;

final class GenerateGenealogyReport
{
    public function __construct(private ?BuildGenealogyReport $builder = null)
    {
        $this->builder ??= new BuildGenealogyReport();
    }

    /** @param array<string, mixed> $parameters */
    public function execute(GenealogyReport $report, array $parameters = []): GenealogyReport
    {
        if ((string) $report->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The report must belong to the active team.');
        }

        $report = DB::transaction(function () use ($report, $parameters): GenealogyReport {
            $output = $this->builder->execute((string) $report->type, (string) $report->team_id, $parameters);
            $metadata = $report->metadata ?? [];
            $metadata['generation'] = [
                'type' => $report->type, 'parameters' => $parameters,
                'generated_at' => now()->toISOString(), 'format' => $parameters['format'] ?? 'json',
            ];
            $report->forceFill(['status' => 'completed', 'metadata' => $metadata, 'generated_output' => $output, 'generated_at' => now()])->save();

            return $report->refresh();
        });
        event(new GenealogyReportUpdated($report));

        return $report;
    }
}
