<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Evidence\Actions\CreateEvidenceRecord;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;

final class EvidenceRecordController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => EvidenceRecord::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateEvidenceRecord $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(EvidenceRecord $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, EvidenceRecord $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(EvidenceRecord $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
