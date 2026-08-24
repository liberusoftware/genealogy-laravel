<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\Evidence\Actions\CreateEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\UpdateEvidenceRecord;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;
use Liberu\Genealogy\GenealogyCore\TeamContext;

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
            'kind' => ['sometimes', Rule::in(EvidenceRecord::KINDS)],
            'repository' => ['nullable', 'string', 'max:255'],
            'citation' => ['nullable', 'string', 'max:10000'],
            'extract' => ['nullable', 'string', 'max:10000'],
            'assertion' => ['nullable', 'string', 'max:10000'],
            'proof_conclusion' => ['nullable', 'string', 'max:10000'],
            'confidence' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'event_date' => ['nullable', 'date'],
            'subject_person_id' => ['nullable', 'uuid', Rule::exists('genealogy_people', 'id')->where('team_id', app(TeamContext::class)->require())],
            'reviewed_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(EvidenceRecord $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, EvidenceRecord $record, UpdateEvidenceRecord $update): JsonResponse
    {
        return response()->json(['data' => $update->execute($record, $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'kind' => ['sometimes', Rule::in(EvidenceRecord::KINDS)],
            'repository' => ['nullable', 'string', 'max:255'],
            'citation' => ['nullable', 'string', 'max:10000'],
            'extract' => ['nullable', 'string', 'max:10000'],
            'assertion' => ['nullable', 'string', 'max:10000'],
            'proof_conclusion' => ['nullable', 'string', 'max:10000'],
            'confidence' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'event_date' => ['nullable', 'date'],
            'subject_person_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('genealogy_people', 'id')->where('team_id', app(TeamContext::class)->require())],
            'reviewed_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]))]);
    }

    public function destroy(EvidenceRecord $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
