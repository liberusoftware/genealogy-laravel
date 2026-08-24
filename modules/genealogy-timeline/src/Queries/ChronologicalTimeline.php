<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Queries;

use Liberu\Genealogy\Timeline\Models\TimelineEvent;

final class ChronologicalTimeline
{
    /** @return list<array<string, mixed>> */
    public function execute(?string $subjectPersonId = null, ?string $familyKey = null, ?string $from = null, ?string $to = null, bool $includePrivate = false): array
    {
        return TimelineEvent::query()
            ->when($subjectPersonId !== null, fn ($query) => $query->where('subject_person_id', $subjectPersonId))
            ->when($familyKey !== null, fn ($query) => $query->where('family_key', $familyKey))
            ->when($from !== null, fn ($query) => $query->where(function ($query) use ($from): void {
                $query->where('date_end', '>=', $from)->orWhere('event_date', '>=', $from);
            }))
            ->when($to !== null, fn ($query) => $query->where(function ($query) use ($to): void {
                $query->where('date_start', '<=', $to)->orWhere('event_date', '<=', $to);
            }))
            ->when(! $includePrivate, fn ($query) => $query->where('is_private', false))
            ->orderByRaw('COALESCE(event_date, date_start, date_end) asc')
            ->orderBy('name')
            ->get()
            ->map(fn (TimelineEvent $event): array => [
                'id' => $event->getKey(), 'kind' => $event->kind, 'name' => $event->name,
                'event_date' => $event->event_date?->toDateString(), 'date_start' => $event->date_start?->toDateString(),
                'date_end' => $event->date_end?->toDateString(), 'date_precision' => $event->date_precision,
                'description' => $event->description, 'historical_context' => $event->historical_context,
                'conflict_group' => $event->conflict_group, 'confidence' => $event->confidence,
                'subject_person_id' => $event->subject_person_id, 'family_key' => $event->family_key,
            ])->values()->all();
    }
}
