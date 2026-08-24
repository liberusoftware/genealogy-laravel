<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Dna\Actions\CreateDnaGroup;
use Liberu\Genealogy\Dna\Models\DnaGroup;

final class DnaGroupController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => DnaGroup::query()->withCount('matches')->latest()->paginate()]);
    }

    public function store(Request $request, CreateDnaGroup $create): JsonResponse
    {
        $group = $create->execute($request->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'status' => ['sometimes', 'string', 'max:50'], 'metadata' => ['nullable', 'array']]));

        return response()->json(['data' => $group], 201);
    }

    public function show(DnaGroup $record): JsonResponse
    {
        return response()->json(['data' => $record->load('matches')]);
    }

    public function update(Request $request, DnaGroup $record): JsonResponse
    {
        $record->update($request->validate(['name' => ['sometimes', 'string', 'max:255'], 'description' => ['sometimes', 'nullable', 'string'], 'status' => ['sometimes', 'string', 'max:50'], 'metadata' => ['sometimes', 'nullable', 'array']]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(DnaGroup $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
