<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationSpace;
use Liberu\Genealogy\Collaboration\Models\CollaborationSpace;

final class CollaborationSpaceController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => CollaborationSpace::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateCollaborationSpace $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(CollaborationSpace $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, CollaborationSpace $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(CollaborationSpace $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
