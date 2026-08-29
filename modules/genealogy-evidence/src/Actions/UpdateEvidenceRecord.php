<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Evidence\Events\EvidenceRecordUpdated;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateEvidenceRecord
{
    /** @param array<string, mixed> $attributes */
    public function execute(EvidenceRecord $record, array $attributes): EvidenceRecord
    {
        if ((string) $record->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The evidence record must belong to the active team.');
        }

        $values = Arr::only($attributes, [
            'name', 'kind', 'repository', 'citation', 'extract', 'assertion', 'proof_conclusion',
            'confidence', 'source_url', 'event_date', 'subject_person_id', 'reviewed_at', 'status', 'metadata',
        ]);
        (new CreateEvidenceRecord())->validate(array_merge($record->toArray(), $values));
        $connection = $record->getConnection();
        $connection->transaction(function () use ($record, $values): void {
            $record->update($values);
        });

        $record = $record->refresh();
        if (app()->bound('events')) {
            event(new EvidenceRecordUpdated($record));
        }

        return $record;
    }
}
