<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Discovery\Actions\CreateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Actions\DeleteDiscoveryMatch;
use Liberu\Genealogy\Discovery\Actions\ReviewDiscoveryMatch;
use Liberu\Genealogy\Discovery\Actions\ScanDuplicateCandidates;
use Liberu\Genealogy\Discovery\Actions\UpdateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Liberu\Genealogy\Discovery\Queries\DiscoverySearch;
use Liberu\Genealogy\Discovery\Queries\DuplicateCandidates;
use Liberu\Genealogy\Discovery\Queries\RelationshipPath;
use Liberu\Genealogy\Discovery\Services\ExternalRecordMatcher;

final class DiscoveryMatchController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
            'kind' => ['sometimes', 'in:'.implode(',', DiscoveryMatch::KINDS)],
            'status' => ['sometimes', 'in:'.implode(',', DiscoveryMatch::STATUSES)],
        ]);
        $perPage = $values['page']['size'] ?? 25;
        $records = DiscoveryMatch::query()->when(isset($values['kind']), fn ($query) => $query->where('kind', $values['kind']))->when(isset($values['status']), fn ($query) => $query->where('status', $values['status']))->latest()->paginate($perPage);

        return response()->json(['data' => $records->getCollection()->map(fn (DiscoveryMatch $record): array => $this->resource($record))->values()->all(), 'meta' => ['current_page' => $records->currentPage(), 'per_page' => $records->perPage(), 'total' => $records->total()]]);
    }

    public function store(Request $request, CreateDiscoveryMatch $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'kind' => ['sometimes', 'in:'.implode(',', DiscoveryMatch::KINDS)],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'in:'.implode(',', DiscoveryMatch::STATUSES)],
            'subject_id' => ['nullable', 'uuid'],
            'related_id' => ['nullable', 'uuid'],
            'confidence' => ['nullable', 'integer', 'between:0,100'],
            'rationale' => ['nullable', 'string', 'max:10000'],
            'source_type' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function show(DiscoveryMatch $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, DiscoveryMatch $record, UpdateDiscoveryMatch $update): JsonResponse
    {
        $values = $request->validate([
            'kind' => ['sometimes', 'in:'.implode(',', DiscoveryMatch::KINDS)],
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:'.implode(',', DiscoveryMatch::STATUSES)],
            'confidence' => ['nullable', 'integer', 'between:0,100'],
            'rationale' => ['nullable', 'string', 'max:10000'],
            'metadata' => ['nullable', 'array'],
        ]);

        return response()->json(['data' => $this->resource($update->execute($record, $values))]);
    }

    public function destroy(DiscoveryMatch $record, DeleteDiscoveryMatch $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    public function review(Request $request, DiscoveryMatch $record, ReviewDiscoveryMatch $review): JsonResponse
    {
        $values = $request->validate(['status' => ['required', 'in:active,completed,dismissed']]);

        return response()->json(['data' => $this->resource($review->execute($record, $values['status']))]);
    }

    public function search(Request $request, DiscoverySearch $search): JsonResponse
    {
        $values = $request->validate(['q' => ['required', 'string', 'min:2', 'max:200'], 'limit' => ['sometimes', 'integer', 'between:1,100'], 'public_only' => ['sometimes', 'boolean'], 'include_living' => ['sometimes', 'boolean'], 'types' => ['sometimes', 'array'], 'types.*' => ['in:people,places,sources']]);

        return response()->json(['data' => $search->execute($values['q'], $values)]);
    }

    public function externalSearch(Request $request, ExternalRecordMatcher $matcher): JsonResponse
    {
        $values = $request->validate([
            'person' => ['required', 'array'],
            'person.first_name' => ['nullable', 'string', 'max:255'], 'person.last_name' => ['nullable', 'string', 'max:255'],
            'person.birth_year' => ['nullable', 'integer', 'between:1,3000'], 'person.birth_place' => ['nullable', 'string', 'max:255'],
            'weights' => ['sometimes', 'array'],
        ]);

        return response()->json(['data' => $matcher->execute($values['person'], $values['weights'] ?? [])]);
    }

    public function duplicates(Request $request, DuplicateCandidates $duplicates): JsonResponse
    {
        $values = $request->validate(['limit' => ['sometimes', 'integer', 'between:1,100']]);

        return response()->json(['data' => $duplicates->execute($values['limit'] ?? 100)]);
    }

    public function scanDuplicates(Request $request, ScanDuplicateCandidates $scan): JsonResponse
    {
        $values = $request->validate([
            'threshold' => ['sometimes', 'numeric', 'between:0,1'],
            'limit' => ['sometimes', 'integer', 'between:1,1000'],
        ]);

        return response()->json(['data' => $scan->execute((float) ($values['threshold'] ?? 0.7), $values['limit'] ?? 100)], 201);
    }

    public function path(Request $request, string $from, string $to, RelationshipPath $path): JsonResponse
    {
        $values = $request->validate(['max_depth' => ['sometimes', 'integer', 'between:1,12'], 'public_only' => ['sometimes', 'boolean']]);

        return response()->json(['data' => $path->execute($from, $to, $values['max_depth'] ?? 6, $values['public_only'] ?? false)]);
    }

    /** @return array<string, mixed> */
    private function resource(DiscoveryMatch $record): array
    {
        return ['id' => $record->getKey(), 'type' => 'genealogy-discovery', 'attributes' => [
            'kind' => $record->kind, 'name' => $record->name, 'subject_id' => $record->subject_id,
            'related_id' => $record->related_id, 'confidence' => $record->confidence, 'rationale' => $record->rationale,
            'source_type' => $record->source_type, 'status' => $record->status, 'metadata' => $record->metadata,
            'detected_at' => $record->detected_at?->toISOString(), 'reviewed_at' => $record->reviewed_at?->toISOString(),
            'created_at' => $record->created_at?->toISOString(), 'updated_at' => $record->updated_at?->toISOString(),
        ]];
    }
}
