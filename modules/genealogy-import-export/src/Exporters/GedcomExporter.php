<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Exporters;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class GedcomExporter
{
    /** @param Collection<int, Person> $people */
    public function export(Collection $people, ?Collection $relationships = null, string $version = '5.5.1'): string
    {
        if (! in_array($version, ['5.5.1', '7.0'], true)) {
            throw new \InvalidArgumentException('The GEDCOM version is not supported.');
        }

        $relationships ??= Relationship::query()->whereIn('type', ['parent', 'partner'])->get();
        $peopleById = $people->keyBy(fn (Person $person): string => (string) $person->getKey());
        $lines = ['0 HEAD', '1 SOUR Genealogy', '1 GEDC', '2 VERS '.$version];
        if ($version === '5.5.1') {
            $lines[] = '1 CHAR UTF-8';
        }
        $families = $this->families($peopleById, $relationships);

        foreach ($people as $person) {
            $xref = $this->xref($person);
            $lines[] = "0 {$xref} INDI";
            $lines[] = '1 NAME '.trim($person->given_name.' /'.($person->family_name ?? '').'/');
            foreach ($person->names as $name) {
                $lines[] = '1 NAME '.trim(($name->given_name ?? '').' /'.($name->family_name ?? '').'/');
            }
            $sex = $this->sex($person->sex, $version);
            if ($sex !== '') {
                $lines[] = '1 SEX '.$sex;
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
                if (in_array((string) $person->getKey(), $family['children'], true)) {
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
            foreach ($family['children'] as $childId) {
                if ($child = $peopleById->get($childId)) {
                    $lines[] = '1 CHIL '.$this->xref($child);
                }
            }
            foreach ($family['events'] as $event) {
                $tag = match ($event['type'] ?? null) {
                    'marriage' => 'MARR',
                    'divorce' => 'DIV',
                    default => null,
                };
                if ($tag === null) {
                    continue;
                }
                $lines[] = '1 '.$tag;
                if (($event['date'] ?? null) !== null) {
                    $lines[] = '2 DATE '.$event['date'];
                }
                if (($event['place'] ?? null) !== null) {
                    $lines[] = '2 PLAC '.$event['place'];
                }
                if (($event['description'] ?? null) !== null) {
                    $lines[] = '2 NOTE '.$event['description'];
                }
            }
        }

        $lines[] = '0 TRLR';

        return implode("\n", $lines)."\n";
    }

    /** @return list<array{xref: string, parents: list<string>, children: list<string>, events: list<array<string, mixed>>}> */
    private function families(Collection $people, Collection $relationships): array
    {
        $families = [];
        $partnerGroups = [];
        foreach ($relationships->where('type', 'partner') as $relationship) {
            $parents = [(string) $relationship->person_id, (string) $relationship->related_person_id];
            if (! $people->has($parents[0]) || ! $people->has($parents[1])) {
                continue;
            }
            sort($parents);
            $key = implode('|', $parents);
            $partnerGroups[$key] = ['parents' => $parents, 'children' => [], 'events' => $relationship->metadata['family_events'] ?? []];
        }

        foreach ($relationships->where('type', 'parent')->groupBy('related_person_id') as $childId => $parentRelationships) {
            if (! $people->has((string) $childId)) {
                continue;
            }
            $parents = $parentRelationships
                ->map(fn (Model $relationship): string => (string) $relationship->person_id)
                ->filter(fn (string $parentId): bool => $people->has($parentId))
                ->unique()
                ->sort()
                ->values()
                ->all();
            if ($parents === []) {
                continue;
            }
            $partnerKey = implode('|', $parents);
            if (isset($partnerGroups[$partnerKey])) {
                $partnerGroups[$partnerKey]['children'][] = (string) $childId;

                continue;
            }
            $key = 'single:'.implode('|', $parents);
            $families[$key] ??= ['parents' => $parents, 'children' => [], 'events' => []];
            $families[$key]['children'][] = (string) $childId;
        }

        foreach ($partnerGroups as $key => $family) {
            $families['partner:'.$key] = $family;
        }

        foreach ($families as $key => $family) {
            $families[$key]['children'] = array_values(array_unique($family['children']));
        }

        return array_values(array_map(fn (array $family): array => [
            'xref' => '@F'.substr(sha1(implode('|', $family['parents']).'|'.implode('|', $family['children'])), 0, 12).'@',
            ...$family,
        ], $families));
    }

    private function xref(Person $person): string
    {
        $xref = $person->metadata['gedcom_xref'] ?? null;

        return is_string($xref) && $xref !== '' ? $xref : '@I'.$person->getKey().'@';
    }

    private function sex(?string $sex, string $version): string
    {
        return match (strtoupper(trim((string) $sex))) {
            'M' => 'M',
            'F' => 'F',
            'X' => $version === '7.0' ? 'X' : 'U',
            'U' => 'U',
            default => '',
        };
    }
}
