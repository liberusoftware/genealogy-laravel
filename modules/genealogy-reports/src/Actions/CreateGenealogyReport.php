<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Reports\Events\GenealogyReportCreated;
use Liberu\Genealogy\Reports\Models\GenealogyReport;

final class CreateGenealogyReport
{
    public function execute(array $attributes): GenealogyReport
    {
        $values = Arr::only($attributes, ['name', 'type', 'status', 'metadata']);
        $this->validate($values);
        $schema = GenealogyReport::query()->getModel()->getConnection()->getSchemaBuilder();
        $values = Arr::only($values, $schema->getColumnListing('genealogy_reports'));

        $report = GenealogyReport::query()->getConnection()->transaction(function () use ($values): GenealogyReport {
            $report = GenealogyReport::query()->create($values);

            return $report;
        });

        if (app()->bound('events')) {
            event(new GenealogyReportCreated($report));
        }

        return $report;
    }

    /** @param array<string, mixed> $values */
    public function validate(array $values): void
    {
        if (trim((string) ($values['name'] ?? '')) === '') {
            throw ValidationException::withMessages(['name' => 'A report name is required.']);
        }
        if (isset($values['type']) && ! in_array($values['type'], GenealogyReport::TYPES, true)) {
            throw ValidationException::withMessages(['type' => 'The report type is not supported.']);
        }
        if (isset($values['status']) && ! in_array($values['status'], GenealogyReport::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'The report status is not supported.']);
        }
    }
}
