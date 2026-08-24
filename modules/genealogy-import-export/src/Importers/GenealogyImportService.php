<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Importers;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class GenealogyImportService
{
    public function __construct(private readonly GenealogyDocumentParser $parser) {}

    /** @return array<string, mixed> */
    public function preview(string $content): array
    {
        $document = $this->parser->parse($content);
        $existing = Person::query()->get();
        $duplicates = 0;

        foreach ($document['people'] as $person) {
            $xref = $person['xref'];
            if ($xref !== null && $existing->contains(fn (Person $row): bool => ($row->metadata['gedcom_xref'] ?? null) === $xref)) {
                $duplicates++;
            }
        }

        return [
            'format' => $document['format'],
            'dry_run' => true,
            'people' => count($document['people']),
            'families' => count($document['families']),
            'relationships' => $this->relationshipCount($document['families']),
            'duplicates' => $duplicates,
            'errors' => $document['errors'],
        ];
    }

    /** @return array<string, mixed> */
    public function import(string $content, bool $dryRun = true, ?DataTransfer $transfer = null): array
    {
        $report = $this->preview($content);

        if ($dryRun) {
            return $report;
        }

        if ($report['errors'] !== []) {
            throw new InvalidArgumentException('The document contains invalid records and cannot be imported.');
        }

        $result = DB::transaction(function () use ($content, $report): array {
            $document = $this->parser->parse($content);
            $people = Person::query()->get();
            $byXref = [];
            $created = 0;
            $updated = 0;

            foreach ($document['people'] as $attributes) {
                $xref = $attributes['xref'];
                $person = $xref === null ? null : $people->first(fn (Person $row): bool => ($row->metadata['gedcom_xref'] ?? null) === $xref);
                $values = [
                    'given_name' => $attributes['given_name'] ?: 'Unknown',
                    'family_name' => $attributes['family_name'],
                    'display_name' => trim(($attributes['given_name'] ?: 'Unknown').' '.($attributes['family_name'] ?? '')),
                    'sex' => $attributes['sex'],
                    'birth_date' => $attributes['birth_date'],
                    'death_date' => $attributes['death_date'],
                    'metadata' => array_merge($person?->metadata ?? [], ['gedcom_xref' => $xref]),
                ];

                if ($person) {
                    $person->update($values);
                    $updated++;
                } else {
                    $person = Person::query()->create($values);
                    $created++;
                }

                if ($xref !== null) {
                    $byXref[$xref] = $person;
                }
            }

            $relationships = 0;
            foreach ($document['families'] as $family) {
                $parents = array_values(array_filter([$family['husband'], $family['wife']], fn (?string $xref): bool => $xref !== null && isset($byXref[$xref])));
                foreach ($family['children'] as $childXref) {
                    if (! isset($byXref[$childXref])) {
                        continue;
                    }
                    foreach ($parents as $parentXref) {
                        Relationship::query()->firstOrCreate([
                            'person_id' => $byXref[$parentXref]->getKey(),
                            'related_person_id' => $byXref[$childXref]->getKey(),
                            'type' => 'parent',
                        ], ['confidence' => 100, 'metadata' => ['gedcom_xref' => $family['xref']]]);
                        $relationships++;
                    }
                }
                if (count($parents) === 2) {
                    Relationship::query()->firstOrCreate([
                        'person_id' => $byXref[$parents[0]]->getKey(),
                        'related_person_id' => $byXref[$parents[1]]->getKey(),
                        'type' => 'partner',
                    ], ['confidence' => 100, 'metadata' => ['gedcom_xref' => $family['xref']]]);
                    $relationships++;
                }
            }

            return array_merge($report, ['dry_run' => false, 'created' => $created, 'updated' => $updated, 'relationships_created' => $relationships]);
        });

        if ($transfer) {
            $transfer->update(['status' => 'completed', 'metadata' => $result]);
        }

        return $result;
    }

    /** @param list<array{children: list<string>}> $families */
    private function relationshipCount(array $families): int
    {
        return array_sum(array_map(fn (array $family): int => count($family['children']), $families));
    }
}
