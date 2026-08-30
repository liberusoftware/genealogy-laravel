<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Actions;

use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\ImportExport\Data\ExportedGenealogyData;
use Liberu\Genealogy\ImportExport\Exporters\GedcomExporter;
use Liberu\Genealogy\ImportExport\Exporters\GedcomXExporter;
use Liberu\Genealogy\ImportExport\Exporters\GrampsExporter;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class ExportGenealogyData
{
    public function __construct(
        private readonly CreateDataTransfer $create,
        private readonly UpdateDataTransfer $update,
        private readonly GedcomExporter $gedcom,
        private readonly GedcomXExporter $gedcomX,
        private readonly GrampsExporter $gramps,
    ) {}

    public function execute(string $format = 'gedcom', string $name = 'Genealogy export'): ExportedGenealogyData
    {
        if (! in_array($format, DataTransfer::FORMATS, true)) {
            throw new InvalidArgumentException('The export format is not supported.');
        }

        app(TeamContext::class)->require();

        $people = Person::query()->with(['names', 'lifeEvents'])->latest()->get();
        $relationships = Relationship::query()->whereIn('type', ['parent', 'partner'])->latest()->get();
        $transfer = $this->create->execute([
            'name' => trim($name) !== '' ? $name : 'Genealogy export',
            'format' => $format,
            'direction' => 'export',
            'records_count' => $people->count(),
            'status' => 'active',
        ]);

        try {
            $content = match ($format) {
                'gramps-xml' => $this->gramps->export($people, $relationships),
                'gedcom-7' => $this->gedcom->export($people, $relationships, '7.0'),
                'gedcom-x' => $this->gedcomX->export($people, $relationships),
                default => $this->gedcom->export($people, $relationships),
            };

            $transfer = $this->update->execute($transfer, [
                'status' => 'completed',
                'metadata' => [
                    'bytes' => strlen($content),
                    'sha256' => hash('sha256', $content),
                    'people' => $people->count(),
                    'relationships' => $relationships->count(),
                    'completed_at' => now()->toISOString(),
                ],
            ]);
        } catch (\Throwable $exception) {
            $this->update->execute($transfer, [
                'status' => 'failed',
                'metadata' => [
                    'failure' => [
                        'message' => mb_substr($exception->getMessage(), 0, 500),
                        'failed_at' => now()->toISOString(),
                    ],
                ],
            ]);

            throw $exception;
        }

        return new ExportedGenealogyData(
            $transfer,
            $content,
            $format,
            match ($format) {
                'gramps-xml' => 'genealogy.gramps.xml',
                'gedcom-x' => 'genealogy.gedcomx.json',
                'gedcom-7' => 'genealogy.ged',
                default => 'genealogy.ged',
            },
            $format === 'gramps-xml' ? 'application/xml; charset=UTF-8' : ($format === 'gedcom-x' ? 'application/json; charset=UTF-8' : 'text/plain; charset=UTF-8'),
        );
    }
}
