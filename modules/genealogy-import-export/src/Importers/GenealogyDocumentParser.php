<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Importers;

use InvalidArgumentException;

/** Converts GEDCOM and the common Gramps XML shape into one record format. */
final class GenealogyDocumentParser
{
    /** @return array{format: string, people: list<array<string, mixed>>, families: list<array<string, mixed>>, errors: list<string>} */
    public function parse(string $content): array
    {
        $trimmed = ltrim($content);

        if (str_starts_with($trimmed, '<')) {
            return $this->parseGrampsXml($trimmed);
        }

        return $this->parseGedcom($content);
    }

    /** @return array{format: string, people: list<array<string, mixed>>, families: list<array<string, mixed>>, errors: list<string>} */
    private function parseGedcom(string $content): array
    {
        $content = ltrim($content, "\xEF\xBB\xBF");
        $records = [];
        $current = null;
        $errors = $this->validateGedcom($content);

        foreach (preg_split('/\R/', $content) ?: [] as $lineNumber => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (! preg_match('/^(\d+)\s+(?:(@[^@]+@)\s+)?([^\s]+)(?:\s+(.*))?$/', $line, $match)) {
                $errors[] = 'Line '.($lineNumber + 1).' is not valid GEDCOM.';

                continue;
            }

            $level = (int) $match[1];
            $value = $match[4] ?? '';

            if ($level === 0) {
                if ($current !== null) {
                    $records[] = $current;
                }

                $current = [
                    'xref' => $match[2] ?? null,
                    'type' => strtoupper($match[3]),
                    'lines' => [],
                ];

                continue;
            }

            if ($current === null) {
                $errors[] = 'Line '.($lineNumber + 1).' appears before a GEDCOM record.';

                continue;
            }

            $current['lines'][] = [
                'level' => $level,
                'tag' => strtoupper($match[3]),
                'value' => $value,
            ];
        }

        if ($current !== null) {
            $records[] = $current;
        }

        $people = [];
        $families = [];
        foreach ($records as $record) {
            if ($record['type'] === 'INDI') {
                $people[] = $this->personFromGedcom($record);
            } elseif ($record['type'] === 'FAM') {
                $families[] = $this->familyFromGedcom($record);
            }
        }

        return ['format' => 'gedcom', 'people' => $people, 'families' => $families, 'errors' => $errors];
    }

    /** @param array{xref: ?string, type: string, lines: list<array{level: int, tag: string, value: string}>} $record */
    private function personFromGedcom(array $record): array
    {
        $person = ['xref' => $record['xref'], 'given_name' => null, 'family_name' => null, 'sex' => null, 'birth_date' => null, 'death_date' => null, 'names' => [], 'life_events' => []];
        $event = null;
        $eventData = [];

        foreach ($record['lines'] as $line) {
            if ($line['level'] === 1) {
                if ($eventData !== []) {
                    $person['life_events'][] = $eventData;
                }
                $event = null;
                $eventData = [];
                if ($line['tag'] === 'NAME') {
                    [$given, $family] = $this->splitName($line['value']);
                    if ($person['given_name'] !== null || $person['family_name'] !== null) {
                        $person['names'][] = ['given_name' => $given, 'family_name' => $family, 'type' => 'alternate'];
                    }
                    $person['given_name'] = $given;
                    $person['family_name'] = $family;
                } elseif ($line['tag'] === 'SEX') {
                    $person['sex'] = strtoupper($line['value']);
                } elseif (in_array($line['tag'], ['BIRT', 'DEAT', 'BURI', 'CREM', 'MARR', 'RESI', 'OCCU', 'EDUC', 'EMIG', 'IMMI', 'CENS', 'PROB', 'WILL', 'GRAD', 'RETI'], true)) {
                    $event = $line['tag'];
                    $eventData = ['type' => match ($line['tag']) {
                        'BIRT' => 'birth',
                        'DEAT' => 'death',
                        'BURI' => 'burial',
                        'CREM' => 'cremation',
                        'MARR' => 'marriage',
                        default => strtolower($line['tag']),
                    }, 'date' => null, 'place' => null, 'description' => null];
                }
            } elseif ($line['level'] >= 2 && $event !== null) {
                if ($line['tag'] === 'DATE') {
                    $date = $this->date($line['value']);
                    if ($event === 'BIRT') {
                        $person['birth_date'] = $date;
                    } elseif ($event === 'DEAT') {
                        $person['death_date'] = $date;
                    }
                    $eventData['date'] = $date;
                } elseif ($line['tag'] === 'PLAC') {
                    $eventData['place'] = trim($line['value']);
                } elseif (in_array($line['tag'], ['NOTE', 'TEXT', 'AGNC', 'TYPE'], true)) {
                    $eventData['description'] = trim($line['value']);
                }
            }

        }

        if ($eventData !== []) {
            $person['life_events'][] = $eventData;
        }

        return $person;
    }

    /** @param array{xref: ?string, type: string, lines: list<array{level: int, tag: string, value: string}>} $record */
    private function familyFromGedcom(array $record): array
    {
        $family = ['xref' => $record['xref'], 'husband' => null, 'wife' => null, 'children' => [], 'events' => []];
        $event = null;
        $eventData = [];

        foreach ($record['lines'] as $line) {
            if ($line['level'] === 1) {
                if ($eventData !== []) {
                    $family['events'][] = $eventData;
                }
                $event = null;
                $eventData = [];
            }

            if ($line['level'] !== 1) {
                if ($line['level'] >= 2 && $event !== null) {
                    if ($line['tag'] === 'DATE') {
                        $eventData['date'] = $this->date($line['value']);
                    } elseif ($line['tag'] === 'PLAC') {
                        $eventData['place'] = trim($line['value']);
                    } elseif (in_array($line['tag'], ['NOTE', 'TEXT', 'AGNC', 'TYPE'], true)) {
                        $eventData['description'] = trim($line['value']);
                    }
                }

                continue;
            }

            if ($line['tag'] === 'HUSB') {
                $family['husband'] = $line['value'];
            } elseif ($line['tag'] === 'WIFE') {
                $family['wife'] = $line['value'];
            } elseif ($line['tag'] === 'CHIL') {
                $family['children'][] = $line['value'];
            } elseif (in_array($line['tag'], ['MARR', 'DIV'], true)) {
                $event = $line['tag'];
                $eventData = ['type' => $line['tag'] === 'MARR' ? 'marriage' : 'divorce', 'date' => null, 'place' => null, 'description' => null];
            }
        }

        if ($eventData !== []) {
            $family['events'][] = $eventData;
        }

        return $family;
    }

    /** @return array{format: string, people: list<array<string, mixed>>, families: list<array<string, mixed>>, errors: list<string>} */
    private function parseGrampsXml(string $content): array
    {
        if (! function_exists('simplexml_load_string')) {
            throw new InvalidArgumentException('GRAMPS XML import requires the SimpleXML PHP extension.');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);

        if ($xml === false) {
            throw new InvalidArgumentException('The uploaded XML document is invalid.');
        }

        $xml->registerXPathNamespace('gramps', 'http://gramps-project.org/xml/1.7.1/');
        $eventsById = [];
        foreach ($xml->xpath('//gramps:event') ?: [] as $event) {
            $id = isset($event['id']) ? (string) $event['id'] : (string) $event['handle'];
            if ($id === '') {
                continue;
            }

            $type = strtolower((string) $event['type']);
            $eventsById[$id] = [
                'type' => $type !== '' ? $type : 'event',
                'date' => $this->date((string) ($event->dateval['val'] ?? '')),
                'place' => trim((string) ($event->placeobj->ptitle ?? $event->place ?? '')) ?: null,
                'description' => trim((string) ($event->description ?? '')) ?: null,
            ];
        }

        $people = [];
        foreach ($xml->xpath('//gramps:person') ?: [] as $person) {
            $name = $person->name;
            $lifeEvents = array_values(array_filter(array_map(
                fn ($eventRef): ?array => $eventsById[(string) (isset($eventRef['hlink']) ? $eventRef['hlink'] : $eventRef['ref'])] ?? null,
                iterator_to_array($person->eventref ?? [], false)
            )));
            $birthDate = collect($lifeEvents)->firstWhere('type', 'birth')['date'] ?? null;
            $deathDate = collect($lifeEvents)->firstWhere('type', 'death')['date'] ?? null;
            $people[] = [
                'xref' => (string) ($person['id'] ?: $person['handle']),
                'given_name' => (string) ($name->first ?: $person->first),
                'family_name' => (string) ($name->surname ?: $person->surname),
                'sex' => strtoupper((string) $person['gender'] ?: (string) $person->gender) ?: null,
                'birth_date' => $birthDate,
                'death_date' => $deathDate,
                'names' => [],
                'life_events' => $lifeEvents,
            ];
        }

        $families = [];
        foreach ($xml->xpath('//gramps:family') ?: [] as $family) {
            $families[] = [
                'xref' => (string) ($family['id'] ?: $family['handle']),
                'husband' => isset($family->father['ref']) ? (string) $family->father['ref'] : null,
                'wife' => isset($family->mother['ref']) ? (string) $family->mother['ref'] : null,
                'children' => array_values(array_map(
                    static fn ($child): string => (string) $child['ref'],
                    iterator_to_array($family->childref ?? [], false)
                )),
                'events' => array_values(array_filter(array_map(
                    fn ($eventRef): ?array => $eventsById[(string) (isset($eventRef['hlink']) ? $eventRef['hlink'] : $eventRef['ref'])] ?? null,
                    iterator_to_array($family->eventref ?? [], false)
                ))),
            ];
        }

        return ['format' => 'gramps-xml', 'people' => $people, 'families' => $families, 'errors' => []];
    }

    /** @return array{0: string, 1: ?string} */
    private function splitName(string $value): array
    {
        $value = trim($value);
        if (preg_match('/^(.*?)\s*\/([^\/]*)\//', $value, $match)) {
            return [trim($match[1]), trim($match[2]) !== '' ? trim($match[2]) : null];
        }

        $parts = preg_split('/\s+/', $value) ?: [];
        $family = array_pop($parts);

        return [trim(implode(' ', $parts)), is_string($family) && $family !== '' ? $family : null];
    }

    private function date(string $value): ?string
    {
        $value = trim((string) preg_replace('/^(ABT|BEF|AFT|EST)\s+/i', '', $value));

        foreach (['!d M Y', '!j M Y', '!M Y', '!Y', '!Y-m-d'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date !== false) {
                return $date->format(in_array($format, ['!Y', '!M Y'], true) ? 'Y-01-01' : 'Y-m-d');
            }
        }

        return null;
    }

    /** @return list<string> */
    private function validateGedcom(string $content): array
    {
        $trimmed = ltrim($content, "\xEF\xBB\xBF \t\r\n");

        if ($trimmed === '') {
            return ['GEDCOM is empty.'];
        }

        $lines = preg_split('/\r\n|\r|\n/', $trimmed) ?: [];
        $errors = [];
        if (! str_starts_with($lines[0] ?? '', '0 HEAD')) {
            $errors[] = 'GEDCOM must begin with a "0 HEAD" record.';
        }
        if (! in_array('0 TRLR', array_map('trim', $lines), true)) {
            $errors[] = 'GEDCOM must contain a "0 TRLR" terminator.';
        }

        return $errors;
    }
}
