<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Platform\PlatformOrchestration\Actions\CreatePlatformWorkflow;
use Liberu\Platform\PlatformOrchestration\Models\PlatformWorkflow;

final class PlatformWorkflowController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => PlatformWorkflow::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreatePlatformWorkflow $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(PlatformWorkflow $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, PlatformWorkflow $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(PlatformWorkflow $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
