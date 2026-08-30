<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Exporters;

use Illuminate\Database\Eloquent\Collection;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class GedcomXExporter
{
    /** @param Collection<int, Person> $people */
    public function export(Collection $people, ?Collection $relationships = null): string
    {
        $relationships ??= Relationship::query()->whereIn('type', ['parent', 'partner'])->get();
        $peopleById = $people->keyBy(fn (Person $person): string => (string) $person->getKey());
        $persons = [];

        foreach ($people as $person) {
            $id = $this->id($person);
            $record = [
                'id' => $id,
                'names' => [[
                    'nameForms' => [[
                        'fullText' => trim((string) $person->given_name.' '.(string) ($person->family_name ?? '')),
                        'parts' => array_values(array_filter([
                            $this->namePart('http://gedcomx.org/Given', $person->given_name),
                            $this->namePart('http://gedcomx.org/Surname', $person->family_name),
                        ])),
                    ]],
                ]],
            ];

            $gender = match (strtoupper((string) $person->sex)) {
                'M' => 'Male',
                'F' => 'Female',
                'X' => 'Intersex',
                default => null,
            };
            if ($gender !== null) {
                $record['gender'] = ['type' => 'http://gedcomx.org/'.$gender];
            }

            foreach ([['birth_date', 'Birth'], ['death_date', 'Death']] as [$column, $type]) {
                if ($person->{$column} === null) {
                    continue;
                }
                $record['facts'][] = [
                    'type' => 'http://gedcomx.org/'.$type,
                    'date' => ['original' => $person->{$column}->format('Y-m-d')],
                ];
            }
            $persons[] = $record;
        }

        $gedcomRelationships = [];
        foreach ($relationships as $relationship) {
            $person1 = $peopleById->get((string) $relationship->person_id);
            $person2 = $peopleById->get((string) $relationship->related_person_id);
            if (! $person1 || ! $person2) {
                continue;
            }
            $gedcomRelationships[] = [
                'type' => 'http://gedcomx.org/'.($relationship->type === 'partner' ? 'Couple' : 'ParentChild'),
                'person1' => ['resource' => '#'.$this->id($person1)],
                'person2' => ['resource' => '#'.$this->id($person2)],
            ];
        }

        return json_encode([
            'persons' => $persons,
            'relationships' => $gedcomRelationships,
            'sourceDescriptions' => [],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)."\n";
    }

    /** @return array{type: string, value: string}|null */
    private function namePart(string $type, mixed $value): ?array
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : ['type' => $type, 'value' => $value];
    }

    private function id(Person $person): string
    {
        $xref = $person->metadata['gedcom_xref'] ?? null;

        return is_string($xref) && $xref !== '' ? trim($xref, '@') : 'p'.$person->getKey();
    }
}
