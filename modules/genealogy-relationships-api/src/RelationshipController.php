<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class RelationshipController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Relationship::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateRelationship $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(Relationship $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, Relationship $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(Relationship $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
