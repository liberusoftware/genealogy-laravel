<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Exporters;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class GrampsExporter
{
    /** @param Collection<int, Person> $people */
    public function export(Collection $people, ?Collection $relationships = null): string
    {
        $relationships ??= Relationship::query()->whereIn('type', ['parent', 'partner'])->get();
        $peopleById = $people->keyBy(fn (Person $person): string => (string) $person->getKey());
        $xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<database xmlns="http://gramps-project.org/xml/1.7.1/">', '  <people>'];

        foreach ($people as $person) {
            $id = $this->id($person);
            $xml[] = '    <person id="'.$this->escape($id).'" type="person">';
            $xml[] = '      <name type="Birth Name"><first>'.$this->escape((string) $person->given_name).'</first><surname>'.$this->escape((string) ($person->family_name ?? '')).'</surname></name>';
            $xml[] = '      <gender>'.$this->escape($this->gender($person->sex)).'</gender>';
            $xml[] = '    </person>';
        }

        $xml[] = '  </people>';
        $families = $this->families($peopleById, $relationships);
        $familyEvents = [];
        foreach ($families as $familyRecord) {
            foreach ($familyRecord['events'] as $event) {
                $familyEvents[md5(serialize($event))] = $event;
            }
        }
        $eventIds = [];
        $xml[] = '  <events>';
        $eventNumber = 1;
        foreach ($familyEvents as $key => $event) {
            $id = 'E'.$eventNumber++;
            $eventIds[$key] = $id;
            $xml[] = '    <event id="'.$id.'" type="'.$this->escape(ucfirst((string) ($event['type'] ?? 'event'))).'">';
            if (($event['date'] ?? null) !== null) {
                $xml[] = '      <dateval val="'.$this->escape((string) $event['date']).'" />';
            }
            if (($event['place'] ?? null) !== null) {
                $xml[] = '      <placeobj><ptitle>'.$this->escape((string) $event['place']).'</ptitle></placeobj>';
            }
            if (($event['description'] ?? null) !== null) {
                $xml[] = '      <description>'.$this->escape((string) $event['description']).'</description>';
            }
            $xml[] = '    </event>';
        }
        $xml[] = '  </events>';
        $xml[] = '  <families>';
        $family = 1;
        foreach ($families as $familyRecord) {
            $xml[] = '    <family id="F'.$family++.'">';
            $parentTags = ['father' => false, 'mother' => false];
            foreach ($familyRecord['parents'] as $parentId) {
                $parentModel = $peopleById->get($parentId);
                if (! $parentModel) {
                    continue;
                }
                $tag = $parentModel->sex === 'F' ? 'mother' : 'father';
                if ($parentTags[$tag]) {
                    $tag = $parentTags['father'] ? 'mother' : 'father';
                }
                $parentTags[$tag] = true;
                $xml[] = '      <'.$tag.' ref="'.$this->escape($this->id($parentModel)).'" />';
            }
            foreach ($familyRecord['children'] as $childId) {
                if ($child = $peopleById->get($childId)) {
                    $xml[] = '      <childref ref="'.$this->escape($this->id($child)).'" />';
                }
            }
            foreach ($familyRecord['events'] as $event) {
                $key = md5(serialize($event));
                if (isset($eventIds[$key])) {
                    $xml[] = '      <eventref hlink="'.$this->escape($eventIds[$key]).'" />';
                }
            }
            $xml[] = '    </family>';
        }
        $xml[] = '  </families>';
        $xml[] = '</database>';

        return implode("\n", $xml)."\n";
    }

    /** @return list<array{parents: list<string>, children: list<string>, events: list<array<string, mixed>>}> */
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

        return array_values($families);
    }

    private function id(Person $person): string
    {
        $xref = $person->metadata['gedcom_xref'] ?? null;

        return is_string($xref) && $xref !== '' ? trim($xref, '@') : 'I'.$person->getKey();
    }

    private function gender(?string $sex): string
    {
        return match (strtoupper((string) $sex)) {
            'M' => 'M',
            'F' => 'F',
            default => 'U',
        };
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
