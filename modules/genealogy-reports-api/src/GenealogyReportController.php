<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Reports\Actions\CreateGenealogyReport;
use Liberu\Genealogy\Reports\Models\GenealogyReport;

final class GenealogyReportController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => GenealogyReport::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateGenealogyReport $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(GenealogyReport $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, GenealogyReport $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(GenealogyReport $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
