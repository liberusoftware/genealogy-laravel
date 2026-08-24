<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Timeline\Actions\CreateTimelineEvent;
use Liberu\Genealogy\Timeline\Models\TimelineEvent;
use Liberu\Genealogy\Timeline\Queries\ChronologicalTimeline;

final class TimelineEventController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('page[size]', 25), 1), 100);
        $events = TimelineEvent::query()->when($request->filled('kind'), fn ($query) => $query->where('kind', $request->string('kind')))->when(! $request->boolean('include_private'), fn ($query) => $query->where('is_private', false))->orderByRaw('COALESCE(event_date, date_start, date_end) desc')->paginate($perPage);

        return response()->json(['data' => $events->through(fn (TimelineEvent $event): array => $this->resource($event)), 'meta' => ['current_page' => $events->currentPage(), 'per_page' => $events->perPage(), 'total' => $events->total()]]);
    }

    public function store(Request $request, CreateTimelineEvent $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'kind' => ['sometimes', 'in:'.implode(',', TimelineEvent::KINDS)],
            'name' => ['required', 'string', 'max:255'],
            'subject_person_id' => ['nullable', 'uuid'],
            'family_key' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'], 'date_start' => ['nullable', 'date'], 'date_end' => ['nullable', 'date', 'after_or_equal:date_start'],
            'date_precision' => ['sometimes', 'in:'.implode(',', TimelineEvent::DATE_PRECISIONS)],
            'place_id' => ['nullable', 'uuid'], 'description' => ['nullable', 'string'], 'historical_context' => ['nullable', 'string'],
            'conflict_group' => ['nullable', 'string', 'max:255'], 'confidence' => ['nullable', 'integer', 'between:0,100'],
            'source_reference' => ['nullable', 'string', 'max:255'], 'is_private' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function show(TimelineEvent $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, TimelineEvent $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'], 'kind' => ['sometimes', 'in:'.implode(',', TimelineEvent::KINDS)],
            'date_precision' => ['sometimes', 'in:'.implode(',', TimelineEvent::DATE_PRECISIONS)], 'event_date' => ['nullable', 'date'],
            'date_start' => ['nullable', 'date'], 'date_end' => ['nullable', 'date', 'after_or_equal:date_start'], 'is_private' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'], 'historical_context' => ['nullable', 'string'], 'conflict_group' => ['nullable', 'string', 'max:255'],
            'confidence' => ['nullable', 'integer', 'between:0,100'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record->refresh())]);
    }

    public function destroy(TimelineEvent $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }

    public function timeline(Request $request, ChronologicalTimeline $timeline): JsonResponse
    {
        $values = $request->validate(['subject_person_id' => ['nullable', 'uuid'], 'family_key' => ['nullable', 'string', 'max:255'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'include_private' => ['sometimes', 'boolean']]);

        return response()->json(['data' => $timeline->execute($values['subject_person_id'] ?? null, $values['family_key'] ?? null, $values['from'] ?? null, $values['to'] ?? null, $values['include_private'] ?? false)]);
    }

    /** @return array<string, mixed> */
    private function resource(TimelineEvent $event): array
    {
        return ['id' => $event->getKey(), 'type' => 'genealogy-timeline-event', 'attributes' => [
            'kind' => $event->kind, 'name' => $event->name, 'subject_person_id' => $event->subject_person_id, 'family_key' => $event->family_key,
            'event_date' => $event->event_date?->toDateString(), 'date_start' => $event->date_start?->toDateString(), 'date_end' => $event->date_end?->toDateString(),
            'date_precision' => $event->date_precision, 'place_id' => $event->place_id, 'description' => $event->description,
            'historical_context' => $event->historical_context, 'conflict_group' => $event->conflict_group, 'confidence' => $event->confidence,
            'source_reference' => $event->source_reference, 'is_private' => $event->is_private, 'status' => $event->status, 'metadata' => $event->metadata,
        ]];
    }
}
