<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Exporters;

use Illuminate\Database\Eloquent\Collection;
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
        $familyEvents = [];
        foreach ($relationships as $relationship) {
            if ($relationship->type !== 'partner') {
                continue;
            }
            foreach ((array) ($relationship->metadata['family_events'] ?? []) as $event) {
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
        foreach ($relationships as $relationship) {
            if (! $peopleById->has((string) $relationship->person_id) || ! $peopleById->has((string) $relationship->related_person_id)) {
                continue;
            }

            $parent = $this->id($peopleById->get((string) $relationship->person_id));
            $related = $this->id($peopleById->get((string) $relationship->related_person_id));
            $xml[] = '    <family id="F'.$family++.'">';
            if ($relationship->type === 'parent') {
                $xml[] = '      <father ref="'.$this->escape($parent).'" />';
                $xml[] = '      <childref ref="'.$this->escape($related).'" />';
            } else {
                $xml[] = '      <father ref="'.$this->escape($parent).'" />';
                $xml[] = '      <mother ref="'.$this->escape($related).'" />';
            }
            foreach ((array) ($relationship->metadata['family_events'] ?? []) as $event) {
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
