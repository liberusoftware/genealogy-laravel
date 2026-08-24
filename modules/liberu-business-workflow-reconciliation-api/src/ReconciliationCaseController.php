<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Platform\BusinessWorkflowReconciliation\Actions\CreateReconciliationCase;
use Liberu\Platform\BusinessWorkflowReconciliation\Models\ReconciliationCase;

final class ReconciliationCaseController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => ReconciliationCase::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateReconciliationCase $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(ReconciliationCase $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, ReconciliationCase $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(ReconciliationCase $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
