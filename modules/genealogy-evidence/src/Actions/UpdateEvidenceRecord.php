<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;

final class UpdateEvidenceRecord
{
    /** @param array<string, mixed> $attributes */
    public function execute(EvidenceRecord $record, array $attributes): EvidenceRecord
    {
        $values = Arr::only($attributes, [
            'name', 'kind', 'repository', 'citation', 'extract', 'assertion', 'proof_conclusion',
            'confidence', 'source_url', 'event_date', 'subject_person_id', 'reviewed_at', 'status', 'metadata',
        ]);
        (new CreateEvidenceRecord())->validate(array_merge($record->toArray(), $values));
        $record->update($values);

        return $record->refresh();
    }
}
