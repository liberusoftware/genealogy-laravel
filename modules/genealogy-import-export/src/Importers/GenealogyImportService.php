<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Importers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\ImportExport\Actions\UpdateDataTransfer;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Actions\CreatePersonLifeEvent;
use Liberu\Genealogy\People\Actions\CreatePersonName;
use Liberu\Genealogy\People\Actions\UpdatePerson;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class GenealogyImportService
{
    public function __construct(
        private readonly GenealogyDocumentParser $parser,
        private readonly ?CreatePerson $createPerson = null,
        private readonly ?CreatePersonName $createPersonName = null,
        private readonly ?CreatePersonLifeEvent $createPersonLifeEvent = null,
        private readonly ?UpdatePerson $updatePerson = null,
        private readonly ?CreateRelationship $createRelationship = null,
        private readonly ?UpdateDataTransfer $updateTransfer = null,
    ) {}

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
            $createdPeople = [];
            $updatedPeople = [];
            $createdRelationships = [];

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
                    $updatedPeople[] = [
                        'id' => (string) $person->getKey(),
                        'attributes' => Arr::only($person->getAttributes(), [
                            'given_name', 'family_name', 'display_name', 'sex', 'aliases', 'attributes',
                            'birth_date', 'death_date', 'birth_place', 'death_place', 'is_public', 'metadata',
                        ]),
                    ];
                    ($this->updatePerson ?? new UpdatePerson())->execute($person, $values);
                    $updated++;
                } else {
                    $person = ($this->createPerson ?? new CreatePerson())->execute($values);
                    $createdPeople[] = (string) $person->getKey();
                    $created++;
                }

                if ($xref !== null) {
                    $byXref[$xref] = $person;
                }

                foreach (($attributes['names'] ?? []) as $name) {
                    $existingName = $person->names()
                        ->where('given_name', $name['given_name'])
                        ->where('family_name', $name['family_name'])
                        ->exists();
                    if (! $existingName) {
                        ($this->createPersonName ?? new CreatePersonName())->execute([
                            'person_id' => $person->getKey(),
                            'type' => $name['type'] ?? 'alternate',
                            'given_name' => $name['given_name'],
                            'family_name' => $name['family_name'],
                        ]);
                    }
                }

                foreach (($attributes['life_events'] ?? []) as $lifeEvent) {
                    $query = $person->lifeEvents()
                        ->where('type', $lifeEvent['type'] ?? 'event')
                        ->whereDate('date', $lifeEvent['date'] ?? null);
                    if (! $query->exists()) {
                        ($this->createPersonLifeEvent ?? new CreatePersonLifeEvent())->execute([
                            'person_id' => $person->getKey(),
                            'type' => $lifeEvent['type'] ?? 'event',
                            'date' => $lifeEvent['date'] ?? null,
                            'place' => $lifeEvent['place'] ?? null,
                            'description' => $lifeEvent['description'] ?? null,
                        ]);
                    }
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
                        if (($relationship = $this->createRelationshipIfMissing([
                            'person_id' => $byXref[$parentXref]->getKey(),
                            'related_person_id' => $byXref[$childXref]->getKey(),
                            'type' => 'parent',
                            'confidence' => 100,
                            'metadata' => ['gedcom_xref' => $family['xref']],
                        ])) !== null) {
                            $createdRelationships[] = (string) $relationship->getKey();
                            $relationships++;
                        }
                    }
                }
                if (count($parents) === 2) {
                    if (($relationship = $this->createRelationshipIfMissing([
                        'person_id' => $byXref[$parents[0]]->getKey(),
                        'related_person_id' => $byXref[$parents[1]]->getKey(),
                        'type' => 'partner',
                        'confidence' => 100,
                        'metadata' => ['gedcom_xref' => $family['xref']],
                    ])) !== null) {
                        $createdRelationships[] = (string) $relationship->getKey();
                        $relationships++;
                    }
                }
            }

            $undo = [
                'expires_at' => now()->addHours((int) config('genealogy-import-export.undo_hours', 24))->toISOString(),
                'created_people' => $createdPeople,
                'updated_people' => $updatedPeople,
                'created_relationships' => $createdRelationships,
            ];

            return array_merge($report, [
                'dry_run' => false,
                'created' => $created,
                'updated' => $updated,
                'relationships_created' => $relationships,
                'undo_expires_at' => $undo['expires_at'],
                'undo' => $undo,
            ]);
        });

        if ($transfer) {
            ($this->updateTransfer ?? new UpdateDataTransfer())->execute($transfer, ['status' => 'completed', 'metadata' => $result]);
        }

        return $result;
    }

    /** @param list<array{children: list<string>}> $families */
    private function relationshipCount(array $families): int
    {
        return array_sum(array_map(function (array $family): int {
            $parents = count(array_filter([$family['husband'] ?? null, $family['wife'] ?? null]));

            return count($family['children']) * $parents + ($parents === 2 ? 1 : 0);
        }, $families));
    }

    /** @param array<string, mixed> $attributes */
    private function createRelationshipIfMissing(array $attributes): ?Relationship
    {
        if (Relationship::query()
            ->where('person_id', $attributes['person_id'])
            ->where('related_person_id', $attributes['related_person_id'])
            ->where('type', $attributes['type'])
            ->exists()) {
            return null;
        }

        return ($this->createRelationship ?? new CreateRelationship())->execute($attributes);
    }
}
