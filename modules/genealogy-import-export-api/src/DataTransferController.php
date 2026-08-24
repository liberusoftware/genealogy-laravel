<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\ImportExport\Actions\CreateDataTransfer;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;

final class DataTransferController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => DataTransfer::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateDataTransfer $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(DataTransfer $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, DataTransfer $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(DataTransfer $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
