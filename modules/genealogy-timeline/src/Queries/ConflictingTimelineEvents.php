<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Queries;

use Liberu\Genealogy\Timeline\Models\TimelineEvent;

final class ConflictingTimelineEvents
{
    /** @return list<array{key: string, events: list<array<string, mixed>>}> */
    public function execute(?string $subjectPersonId = null, bool $includePrivate = false): array
    {
        $events = TimelineEvent::query()
            ->where(function ($query): void {
                $query->whereNotNull('conflict_group')
                    ->orWhere(fn ($nested) => $nested->whereNotNull('subject_person_id')->whereNotNull('event_date'));
            })
            ->when($subjectPersonId !== null, fn ($query) => $query->where('subject_person_id', $subjectPersonId))
            ->when(! $includePrivate, fn ($query) => $query->where('is_private', false))
            ->orderBy('event_date')
            ->get();

        $groups = $events->groupBy(fn (TimelineEvent $event): string => (string) ($event->conflict_group ?: $event->subject_person_id.'|'.$event->event_date?->toDateString()))
            ->filter(fn ($group): bool => $group->count() > 1);

        return $groups->map(fn ($group, string $key): array => [
            'key' => $key,
            'events' => $group->map(fn (TimelineEvent $event): array => [
                'id' => (string) $event->getKey(), 'name' => $event->name, 'kind' => $event->kind,
                'event_date' => $event->event_date?->toDateString(), 'confidence' => $event->confidence,
                'source_reference' => $event->source_reference,
            ])->values()->all(),
        ])->values()->all();
    }
}
