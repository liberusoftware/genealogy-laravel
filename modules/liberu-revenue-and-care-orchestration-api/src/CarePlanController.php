<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Platform\RevenueAndCareOrchestration\Actions\CreateCarePlan;
use Liberu\Platform\RevenueAndCareOrchestration\Models\CarePlan;

final class CarePlanController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => CarePlan::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateCarePlan $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(CarePlan $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, CarePlan $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(CarePlan $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
