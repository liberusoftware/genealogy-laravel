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
        $records = [];
        $current = null;
        $errors = [];

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
        $person = ['xref' => $record['xref'], 'given_name' => null, 'family_name' => null, 'sex' => null, 'birth_date' => null, 'death_date' => null];
        $event = null;

        foreach ($record['lines'] as $line) {
            if ($line['level'] === 1) {
                $event = null;
                if ($line['tag'] === 'NAME') {
                    [$given, $family] = $this->splitName($line['value']);
                    $person['given_name'] = $given;
                    $person['family_name'] = $family;
                } elseif ($line['tag'] === 'SEX') {
                    $person['sex'] = strtoupper($line['value']);
                } elseif ($line['tag'] === 'BIRT' || $line['tag'] === 'DEAT') {
                    $event = $line['tag'];
                }
            } elseif ($line['level'] >= 2 && $line['tag'] === 'DATE' && $event !== null) {
                $person[$event === 'BIRT' ? 'birth_date' : 'death_date'] = $this->date($line['value']);
            }
        }

        return $person;
    }

    /** @param array{xref: ?string, type: string, lines: list<array{level: int, tag: string, value: string}>} $record */
    private function familyFromGedcom(array $record): array
    {
        $family = ['xref' => $record['xref'], 'husband' => null, 'wife' => null, 'children' => []];

        foreach ($record['lines'] as $line) {
            if ($line['level'] !== 1) {
                continue;
            }

            if ($line['tag'] === 'HUSB') {
                $family['husband'] = $line['value'];
            } elseif ($line['tag'] === 'WIFE') {
                $family['wife'] = $line['value'];
            } elseif ($line['tag'] === 'CHIL') {
                $family['children'][] = $line['value'];
            }
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

        $people = [];
        foreach ($xml->xpath('//person') ?: [] as $person) {
            $name = $person->name;
            $people[] = [
                'xref' => (string) ($person['id'] ?: $person['handle']),
                'given_name' => (string) ($name->first ?: $person->first),
                'family_name' => (string) ($name->surname ?: $person->surname),
                'sex' => strtoupper((string) $person['gender'] ?: (string) $person->gender) ?: null,
                'birth_date' => null,
                'death_date' => null,
            ];
        }

        return ['format' => 'gramps-xml', 'people' => $people, 'families' => [], 'errors' => []];
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

        foreach (['!d M Y', '!M Y', '!Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date !== false) {
                return $date->format($format === '!Y' ? 'Y-01-01' : 'Y-m-d');
            }
        }

        return null;
    }
}
