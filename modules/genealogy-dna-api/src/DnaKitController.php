<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Dna\Actions\CreateDnaKit;
use Liberu\Genealogy\Dna\Models\DnaKit;

final class DnaKitController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => DnaKit::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateDnaKit $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(DnaKit $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, DnaKit $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(DnaKit $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
