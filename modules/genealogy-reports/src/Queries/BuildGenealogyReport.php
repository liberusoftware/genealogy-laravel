<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Queries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Builds report data from module-owned tables without importing another
 * genealogy module. Missing optional modules produce an empty section rather
 * than making report generation unavailable.
 */
final class BuildGenealogyReport
{
    /** @return array{format: string, content: array<string, mixed>|string, rows: int} */
    public function execute(string $type, string $teamId, array $parameters = []): array
    {
        $format = (string) ($parameters['format'] ?? 'json');
        $payload = match ($type) {
            'sources' => ['sources' => $this->rows('genealogy_evidence_sources', $teamId, ['id', 'name', 'record_type', 'url'])],
            'research' => $this->research($teamId),
            'timeline' => ['events' => $this->rows('timeline_events', $teamId, ['id', 'name', 'kind', 'subject_person_id', 'event_date', 'description'])],
            default => $this->peopleGraph($teamId, $parameters, $type),
        };

        $content = match ($format) {
            'csv' => $this->csv($payload),
            'gedcom' => $this->gedcom($payload),
            'svg' => $this->svg($payload),
            default => $payload,
        };

        return ['format' => $format, 'content' => $content, 'rows' => $this->countRows($payload)];
    }

    /** @param list<string> $columns @return list<array<string, mixed>> */
    private function rows(string $table, string $teamId, array $columns): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)->where('team_id', $teamId)->latest()->get($columns)->map(fn ($row): array => (array) $row)->all();
    }

    /** @return array<string, mixed> */
    private function research(string $teamId): array
    {
        $projects = $this->rows('research_projects', $teamId, ['id', 'name', 'status']);
        $entries = $this->rows('research_entries', $teamId, ['id', 'research_project_id', 'kind', 'title', 'status', 'due_date']);

        return ['projects' => $projects, 'entries' => $entries];
    }

    /** @return array<string, mixed> */
    private function peopleGraph(string $teamId, array $parameters, string $type): array
    {
        $people = $this->rows('genealogy_people', $teamId, ['id', 'given_name', 'family_name', 'display_name', 'birth_date', 'death_date']);
        $relationships = $this->rows('genealogy_relationships', $teamId, ['id', 'person_id', 'related_person_id', 'type', 'confidence']);
        $rootId = isset($parameters['root_person_id']) ? (string) $parameters['root_person_id'] : null;

        if ($rootId !== null) {
            $ids = [$rootId => true];
            $frontier = [$rootId];
            while ($frontier !== []) {
                $next = [];
                foreach ($relationships as $relationship) {
                    if ($relationship['type'] !== 'parent') {
                        continue;
                    }
                    $ancestorsOnly = $type === 'pedigree';
                    $descendantsOnly = $type === 'descendants';
                    if (! $descendantsOnly && in_array($relationship['related_person_id'], $frontier, true)) {
                        $id = (string) $relationship['person_id'];
                    } elseif (! $ancestorsOnly && in_array($relationship['person_id'], $frontier, true)) {
                        $id = (string) $relationship['related_person_id'];
                    } else {
                        continue;
                    }
                    if (! isset($ids[$id])) {
                        $ids[$id] = true;
                        $next[] = $id;
                    }
                }
                $frontier = $next;
            }
            if ($type === 'family_group') {
                $expanded = true;
                while ($expanded) {
                    $expanded = false;
                    foreach ($relationships as $relationship) {
                        if ($relationship['type'] !== 'partner') {
                            continue;
                        }
                        $left = (string) $relationship['person_id'];
                        $right = (string) $relationship['related_person_id'];
                        if (isset($ids[$left]) && ! isset($ids[$right])) {
                            $ids[$right] = true;
                            $expanded = true;
                        } elseif (isset($ids[$right]) && ! isset($ids[$left])) {
                            $ids[$left] = true;
                            $expanded = true;
                        }
                    }
                }
            }
            $people = array_values(array_filter($people, fn (array $person): bool => isset($ids[(string) $person['id']])));
            $relationships = array_values(array_filter($relationships, fn (array $relationship): bool => isset($ids[(string) $relationship['person_id']]) && isset($ids[(string) $relationship['related_person_id']])));
        }

        return ['people' => $people, 'relationships' => $relationships, 'root_person_id' => $rootId];
    }

    /** @param array<string, mixed> $payload */
    private function countRows(array $payload): int
    {
        return array_sum(array_map(static fn (mixed $value): int => is_countable($value) ? count($value) : 0, $payload));
    }

    /** @param array<string, mixed> $payload */
    private function csv(array $payload): string
    {
        $rows = [];
        foreach ($payload as $section => $values) {
            if (! is_array($values)) {
                continue;
            }
            foreach ($values as $value) {
                if (! is_array($value)) {
                    continue;
                }
                $rows[] = array_merge(['section' => $section], $value);
            }
        }
        if ($rows === []) {
            return "section\n";
        }
        $headers = array_values(array_unique(array_merge(...array_map('array_keys', $rows))));
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $headers);
        foreach ($rows as $row) {
            fputcsv($stream, array_map(static fn (mixed $value): mixed => is_array($value) ? json_encode($value) : $value, array_map(fn (string $header): mixed => $row[$header] ?? null, $headers)));
        }
        rewind($stream);

        return stream_get_contents($stream) ?: '';
    }

    /** @param array<string, mixed> $payload */
    private function gedcom(array $payload): string
    {
        $lines = ['0 HEAD', '1 SOUR Liberu Genealogy'];
        foreach (($payload['people'] ?? []) as $person) {
            $lines[] = '0 @'.strtoupper(substr((string) $person['id'], 0, 8)).'@ INDI';
            $lines[] = '1 NAME '.trim((string) ($person['display_name'] ?: (($person['given_name'] ?? '').' '.($person['family_name'] ?? ''))));
            if ($person['birth_date'] !== null) {
                $lines[] = '1 BIRT';
                $lines[] = '2 DATE '.$person['birth_date'];
            }
            if ($person['death_date'] !== null) {
                $lines[] = '1 DEAT';
                $lines[] = '2 DATE '.$person['death_date'];
            }
        }
        $lines[] = '0 TRLR';

        return implode("\n", $lines)."\n";
    }

    /** @param array<string, mixed> $payload */
    private function svg(array $payload): string
    {
        $labels = [];
        foreach (($payload['people'] ?? []) as $person) {
            $labels[] = htmlspecialchars((string) ($person['display_name'] ?: trim(($person['given_name'] ?? '').' '.($person['family_name'] ?? ''))), ENT_XML1);
        }
        $height = max(40, count($labels) * 28 + 20);
        $text = array_map(fn (int $index, string $label): string => '<text x="12" y="'.(28 + $index * 28).'">'.$label.'</text>', array_keys($labels), $labels);

        return '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="'.$height.'">'.implode('', $text).'</svg>';
    }
}
