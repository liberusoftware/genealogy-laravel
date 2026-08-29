<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Dna\Actions\CreateDnaGroup;
use Liberu\Genealogy\Dna\Actions\DeleteDnaGroup;
use Liberu\Genealogy\Dna\Actions\UpdateDnaGroup;
use Liberu\Genealogy\Dna\Models\DnaGroup;

final class DnaGroupController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
        ]);
        $pageSize = $values['page']['size'] ?? $values['page[size]'] ?? 25;
        $groups = DnaGroup::query()->withCount('matches')->latest()->paginate($pageSize);

        return response()->json([
            'data' => $groups->getCollection()->map(fn (DnaGroup $group): array => $this->resource($group))->values()->all(),
            'meta' => ['current_page' => $groups->currentPage(), 'per_page' => $groups->perPage(), 'total' => $groups->total()],
        ]);
    }

    public function store(Request $request, CreateDnaGroup $create): JsonResponse
    {
        $group = $create->execute($request->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'status' => ['sometimes', 'in:'.implode(',', DnaGroup::STATUSES)], 'metadata' => ['nullable', 'array']]));

        return response()->json(['data' => $this->resource($group)], 201);
    }

    public function show(DnaGroup $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record->load('matches'))]);
    }

    public function update(Request $request, DnaGroup $record, UpdateDnaGroup $update): JsonResponse
    {
        $values = $request->validate(['name' => ['sometimes', 'string', 'max:255'], 'description' => ['sometimes', 'nullable', 'string'], 'status' => ['sometimes', 'in:'.implode(',', DnaGroup::STATUSES)], 'metadata' => ['sometimes', 'nullable', 'array']]);

        return response()->json(['data' => $this->resource($update->execute($record, $values))]);
    }

    public function destroy(DnaGroup $record, DeleteDnaGroup $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    /** @return array<string, mixed> */
    private function resource(DnaGroup $group): array
    {
        return ['id' => $group->getKey(), 'type' => 'genealogy-dna-group', 'attributes' => [
            'name' => $group->name, 'description' => $group->description, 'status' => $group->status,
            'metadata' => $group->metadata, 'matches_count' => $group->matches_count ?? null,
            'matches' => $group->relationLoaded('matches') ? $group->matches->map(fn ($match): array => [
                'id' => $match->getKey(), 'external_id' => $match->external_id, 'status' => $match->status,
            ])->values()->all() : null,
            'created_at' => $group->created_at?->toISOString(), 'updated_at' => $group->updated_at?->toISOString(),
        ]];
    }
}
