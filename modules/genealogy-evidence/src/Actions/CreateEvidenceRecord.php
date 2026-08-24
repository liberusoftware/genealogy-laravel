<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;

final class CreateEvidenceRecord
{
    public function execute(array $attributes): EvidenceRecord
    {
        $values = Arr::only($attributes, [
            'name', 'kind', 'repository', 'citation', 'extract', 'assertion', 'proof_conclusion',
            'confidence', 'source_url', 'event_date', 'subject_person_id', 'reviewed_at', 'status', 'metadata',
        ]);
        $this->validate($values);

        return EvidenceRecord::query()->create($values);
    }

    /** @param array<string, mixed> $values */
    public function validate(array $values): void
    {
        if (! in_array($values['kind'] ?? 'source', EvidenceRecord::KINDS, true)) {
            throw new InvalidArgumentException('The evidence kind is not supported.');
        }

        if ((int) ($values['confidence'] ?? 0) < 0 || (int) ($values['confidence'] ?? 0) > 100) {
            throw new InvalidArgumentException('Evidence confidence must be between 0 and 100.');
        }
    }
}
