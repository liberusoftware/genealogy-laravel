<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Exporters;

use Illuminate\Database\Eloquent\Collection;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class GedcomExporter
{
    /** @param Collection<int, Person> $people */
    public function export(Collection $people, ?Collection $relationships = null): string
    {
        $relationships ??= Relationship::query()->whereIn('type', ['parent', 'partner'])->get();
        $peopleById = $people->keyBy(fn (Person $person): string => (string) $person->getKey());
        $lines = ['0 HEAD', '1 SOUR Genealogy', '1 GEDC', '2 VERS 5.5.1', '1 CHAR UTF-8'];
        $families = $this->families($peopleById, $relationships);

        foreach ($people as $person) {
            $xref = $this->xref($person);
            $lines[] = "0 {$xref} INDI";
            $lines[] = '1 NAME '.trim($person->given_name.' /'.($person->family_name ?? '').'/');
            foreach ($person->names as $name) {
                $lines[] = '1 NAME '.trim(($name->given_name ?? '').' /'.($name->family_name ?? '').'/');
            }
            if ($person->sex) {
                $lines[] = '1 SEX '.$person->sex;
            }
            if ($person->birth_date) {
                $lines[] = '1 BIRT';
                $lines[] = '2 DATE '.$person->birth_date->format('d M Y');
            }
            if ($person->death_date) {
                $lines[] = '1 DEAT';
                $lines[] = '2 DATE '.$person->death_date->format('d M Y');
            }
            foreach ($person->lifeEvents as $lifeEvent) {
                if (in_array($lifeEvent->type, ['birth', 'death'], true)) {
                    continue;
                }
                $tag = match ($lifeEvent->type) {
                    'burial' => 'BURI',
                    'cremation' => 'CREM',
                    'marriage' => 'MARR',
                    default => strtoupper((string) $lifeEvent->type),
                };
                $lines[] = '1 '.$tag;
                if ($lifeEvent->date) {
                    $lines[] = '2 DATE '.$lifeEvent->date->format('d M Y');
                }
                if ($lifeEvent->place) {
                    $lines[] = '2 PLAC '.$lifeEvent->place;
                }
                if ($lifeEvent->description) {
                    $lines[] = '2 NOTE '.$lifeEvent->description;
                }
            }
            foreach ($families as $family) {
                if (in_array((string) $person->getKey(), $family['parents'], true)) {
                    $lines[] = '1 FAMS '.$family['xref'];
                }
                if ($family['child'] === (string) $person->getKey()) {
                    $lines[] = '1 FAMC '.$family['xref'];
                }
            }
        }

        foreach ($families as $family) {
            $lines[] = "0 {$family['xref']} FAM";
            foreach ($family['parents'] as $parentId) {
                $parent = $peopleById->get($parentId);
                if (! $parent) {
                    continue;
                }
                $lines[] = '1 '.($parent->sex === 'F' ? 'WIFE ' : 'HUSB ').$this->xref($parent);
            }
            if ($family['child'] !== null && ($child = $peopleById->get($family['child']))) {
                $lines[] = '1 CHIL '.$this->xref($child);
            }
        }

        $lines[] = '0 TRLR';

        return implode("\n", $lines)."\n";
    }

    /** @return list<array{xref: string, parents: list<string>, child: ?string}> */
    private function families(Collection $people, Collection $relationships): array
    {
        $families = [];
        foreach ($relationships as $relationship) {
            if (! $people->has($relationship->person_id) || ! $people->has($relationship->related_person_id)) {
                continue;
            }
            if ($relationship->type === 'parent') {
                $families[] = ['xref' => '@F'.substr(sha1($relationship->person_id.$relationship->related_person_id), 0, 12).'@', 'parents' => [(string) $relationship->person_id], 'child' => (string) $relationship->related_person_id];
            } elseif ($relationship->type === 'partner') {
                $families[] = ['xref' => '@F'.substr(sha1($relationship->person_id.$relationship->related_person_id), 0, 12).'@', 'parents' => [(string) $relationship->person_id, (string) $relationship->related_person_id], 'child' => null];
            }
        }

        return $families;
    }

    private function xref(Person $person): string
    {
        $xref = $person->metadata['gedcom_xref'] ?? null;

        return is_string($xref) && $xref !== '' ? $xref : '@I'.$person->getKey().'@';
    }
}
