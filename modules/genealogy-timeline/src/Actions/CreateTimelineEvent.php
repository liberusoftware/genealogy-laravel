<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Timeline\Models\TimelineEvent;

final class CreateTimelineEvent
{
    public function execute(array $attributes): TimelineEvent
    {
        $values = Arr::only($attributes, ['kind', 'name', 'subject_person_id', 'family_key', 'event_date', 'date_start', 'date_end', 'date_precision', 'place_id', 'description', 'historical_context', 'conflict_group', 'confidence', 'source_reference', 'is_private', 'status', 'metadata']);
        if (isset($values['kind']) && ! in_array($values['kind'], TimelineEvent::KINDS, true)) {
            throw ValidationException::withMessages(['kind' => 'The selected timeline event kind is invalid.']);
        }
        if (isset($values['date_precision']) && ! in_array($values['date_precision'], TimelineEvent::DATE_PRECISIONS, true)) {
            throw ValidationException::withMessages(['date_precision' => 'The selected date precision is invalid.']);
        }
        if (isset($values['confidence']) && ($values['confidence'] < 0 || $values['confidence'] > 100)) {
            throw ValidationException::withMessages(['confidence' => 'Confidence must be between 0 and 100.']);
        }
        if (isset($values['date_start'], $values['date_end']) && $values['date_start'] > $values['date_end']) {
            throw ValidationException::withMessages(['date_end' => 'The end date cannot precede the start date.']);
        }

        return TimelineEvent::query()->create($values);
    }
}
