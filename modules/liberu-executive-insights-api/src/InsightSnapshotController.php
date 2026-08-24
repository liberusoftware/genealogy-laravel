<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Platform\ExecutiveInsights\Actions\CreateInsightSnapshot;
use Liberu\Platform\ExecutiveInsights\Models\InsightSnapshot;

final class InsightSnapshotController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => InsightSnapshot::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateInsightSnapshot $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(InsightSnapshot $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, InsightSnapshot $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(InsightSnapshot $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
