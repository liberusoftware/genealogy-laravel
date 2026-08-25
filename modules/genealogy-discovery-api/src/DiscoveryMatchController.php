<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Discovery\Actions\CreateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Liberu\Genealogy\Discovery\Queries\DiscoverySearch;
use Liberu\Genealogy\Discovery\Queries\DuplicateCandidates;
use Liberu\Genealogy\Discovery\Queries\RelationshipPath;

final class DiscoveryMatchController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('page[size]', 25), 1), 100);
        $records = DiscoveryMatch::query()->when($request->filled('kind'), fn ($query) => $query->where('kind', $request->string('kind')))->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))->latest()->paginate($perPage);

        return response()->json(['data' => $records->through(fn (DiscoveryMatch $record): array => $this->resource($record)), 'meta' => ['current_page' => $records->currentPage(), 'per_page' => $records->perPage(), 'total' => $records->total()]]);
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

    public function update(Request $request, DiscoveryMatch $record): JsonResponse
    {
        $record->update($request->validate([
            'kind' => ['sometimes', 'in:'.implode(',', DiscoveryMatch::KINDS)],
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:'.implode(',', DiscoveryMatch::STATUSES)],
            'confidence' => ['nullable', 'integer', 'between:0,100'],
            'rationale' => ['nullable', 'string', 'max:10000'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record->refresh())]);
    }

    public function destroy(DiscoveryMatch $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }

    public function search(Request $request, DiscoverySearch $search): JsonResponse
    {
        $values = $request->validate(['q' => ['required', 'string', 'min:2', 'max:200'], 'limit' => ['sometimes', 'integer', 'between:1,100'], 'public_only' => ['sometimes', 'boolean'], 'include_living' => ['sometimes', 'boolean'], 'types' => ['sometimes', 'array'], 'types.*' => ['in:people,places,sources']]);

        return response()->json(['data' => $search->execute($values['q'], $values)]);
    }

    public function duplicates(Request $request, DuplicateCandidates $duplicates): JsonResponse
    {
        $values = $request->validate(['limit' => ['sometimes', 'integer', 'between:1,100']]);

        return response()->json(['data' => $duplicates->execute($values['limit'] ?? 100)]);
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
