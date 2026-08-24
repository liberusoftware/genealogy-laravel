<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Places\Actions\CreatePlace;
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
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(Place $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, Place $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(Place $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
