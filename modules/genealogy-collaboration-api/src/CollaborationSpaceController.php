<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationSpace;
use Liberu\Genealogy\Collaboration\Actions\DeleteCollaborationSpace;
use Liberu\Genealogy\Collaboration\Actions\UpdateCollaborationSpace;
use Liberu\Genealogy\Collaboration\Models\CollaborationSpace;

final class CollaborationSpaceController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate(['page' => ['sometimes', 'array'], 'page.size' => ['sometimes', 'integer', 'between:1,100']]);
        $spaces = CollaborationSpace::query()->latest()->paginate($values['page']['size'] ?? 25);

        return response()->json(['data' => $spaces->getCollection()->map(fn (CollaborationSpace $space): array => $this->resource($space))->values()->all(), 'meta' => ['current_page' => $spaces->currentPage(), 'per_page' => $spaces->perPage(), 'total' => $spaces->total()]]);
    }

    public function store(Request $request, CreateCollaborationSpace $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'in:'.implode(',', CollaborationSpace::STATUSES)],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function show(CollaborationSpace $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, CollaborationSpace $record, UpdateCollaborationSpace $update): JsonResponse
    {
        $values = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:'.implode(',', CollaborationSpace::STATUSES)],
            'metadata' => ['nullable', 'array'],
        ]);

        return response()->json(['data' => $this->resource($update->execute($record, $values))]);
    }

    public function destroy(CollaborationSpace $record, DeleteCollaborationSpace $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    /** @return array<string, mixed> */
    private function resource(CollaborationSpace $space): array
    {
        return [
            'id' => $space->getKey(),
            'type' => 'genealogy-collaboration-space',
            'attributes' => [
                'name' => $space->name,
                'status' => $space->status,
                'metadata' => $space->metadata,
                'created_at' => $space->created_at?->toISOString(),
                'updated_at' => $space->updated_at?->toISOString(),
            ],
        ];
    }
}
