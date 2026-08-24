<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Places\Actions\CreatePlace;
use Liberu\Genealogy\Places\Actions\UpdatePlace;
use Liberu\Genealogy\Places\Models\Place;

final class PlaceController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Place::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreatePlace $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'uuid'],
            'historical_names' => ['nullable', 'array'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'jurisdiction' => ['nullable', 'string', 'max:255'],
            'is_current' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(Place $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, Place $record, UpdatePlace $update): JsonResponse
    {
        return response()->json(['data' => $update->execute($record, $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'parent_id' => ['sometimes', 'nullable', 'uuid'],
            'historical_names' => ['nullable', 'array'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'jurisdiction' => ['nullable', 'string', 'max:255'],
            'is_current' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]))]);
    }

    public function destroy(Place $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
