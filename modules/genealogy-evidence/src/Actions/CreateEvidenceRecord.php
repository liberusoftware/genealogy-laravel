<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Evidence\Confidence;
use Liberu\Genealogy\Evidence\Events\EvidenceRecordCreated;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Support\PersonReference;

final class CreateEvidenceRecord
{
    public function execute(array $attributes): EvidenceRecord
    {
        $values = Arr::only($attributes, [
            'name', 'kind', 'repository', 'citation', 'extract', 'assertion', 'proof_conclusion',
            'confidence', 'source_url', 'event_date', 'subject_person_id', 'reviewed_at', 'status', 'metadata',
        ]);
        $this->validate($values);
        $values['name'] = trim((string) $values['name']);

        $schema = EvidenceRecord::query()->getModel()->getConnection()->getSchemaBuilder();
        $values = Arr::only($values, $schema->getColumnListing('evidence_records'));
        if ($schema->hasColumn('evidence_records', 'team_id')) {
            $values['team_id'] = app(TeamContext::class)->require();
        }

        $connection = EvidenceRecord::query()->getModel()->getConnection();

        $record = $connection->transaction(function () use ($values): EvidenceRecord {
            $record = EvidenceRecord::query()->create($values);

            return $record;
        });

        if (app()->bound('events')) {
            event(new EvidenceRecordCreated($record));
        }

        return $record;
    }

    /** @param array<string, mixed> $values */
    public function validate(array $values): void
    {
        $name = trim((string) ($values['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('An evidence name is required.');
        }

        if (! in_array($values['kind'] ?? 'source', EvidenceRecord::KINDS, true)) {
            throw new InvalidArgumentException('The evidence kind is not supported.');
        }

        Confidence::from((int) ($values['confidence'] ?? 0));

        if (isset($values['status']) && ! in_array($values['status'], EvidenceRecord::STATUSES, true)) {
            throw new InvalidArgumentException('The evidence status is not supported.');
        }

        if (filled($values['proof_conclusion'] ?? null) && blank($values['assertion'] ?? null)) {
            throw new InvalidArgumentException('A proof conclusion requires an assertion.');
        }

        if (isset($values['subject_person_id'])) {
            app(PersonReference::class)->require($values['subject_person_id']);
        }
    }
}
