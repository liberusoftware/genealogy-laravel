<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Research\Actions\CreateResearchProject;
use Liberu\Genealogy\Research\Models\ResearchProject;

final class ResearchProjectController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => ResearchProject::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateResearchProject $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(ResearchProject $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, ResearchProject $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(ResearchProject $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
