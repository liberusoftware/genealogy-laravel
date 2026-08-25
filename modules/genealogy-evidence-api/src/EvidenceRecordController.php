<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\Evidence\Actions\ArchiveEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\CreateEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\DeleteEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\ReviewEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\UpdateEvidenceRecord;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class EvidenceRecordController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'kind' => ['sometimes', Rule::in(EvidenceRecord::KINDS)],
            'status' => ['sometimes', Rule::in(EvidenceRecord::STATUSES)],
            'min_confidence' => ['sometimes', 'integer', 'between:0,100'],
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
        ]);
        $records = EvidenceRecord::query()
            ->when(isset($values['kind']), fn ($query) => $query->where('kind', $values['kind']))
            ->when(isset($values['status']), fn ($query) => $query->where('status', $values['status']))
            ->when(isset($values['min_confidence']), fn ($query) => $query->where('confidence', '>=', $values['min_confidence']))
            ->latest()
            ->paginate($values['page']['size'] ?? 25);

        return response()->json([
            'data' => $records->getCollection()->map(fn (EvidenceRecord $record): array => $this->resource($record))->values()->all(),
            'meta' => ['current_page' => $records->currentPage(), 'per_page' => $records->perPage(), 'total' => $records->total()],
        ]);
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
            'status' => ['sometimes', Rule::in(EvidenceRecord::STATUSES)],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function show(string $record): JsonResponse
    {
        $record = $this->record($record);

        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, string $record, UpdateEvidenceRecord $update): JsonResponse
    {
        $record = $this->record($record);
        $values = $request->validate([
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
            'status' => ['sometimes', Rule::in(EvidenceRecord::STATUSES)],
            'metadata' => ['nullable', 'array'],
        ]);

        return response()->json(['data' => $this->resource($update->execute($record, $values))]);
    }

    public function destroy(string $record, DeleteEvidenceRecord $delete): JsonResponse
    {
        $record = $this->record($record);
        $delete->execute($record);

        return response()->json(status: 204);
    }

    public function review(string $record, ReviewEvidenceRecord $review): JsonResponse
    {
        $record = $this->record($record);

        return response()->json(['data' => $this->resource($review->execute($record))]);
    }

    public function archive(string $record, ArchiveEvidenceRecord $archive): JsonResponse
    {
        $record = $this->record($record);

        return response()->json(['data' => $this->resource($archive->execute($record))]);
    }

    private function record(string $id): EvidenceRecord
    {
        return EvidenceRecord::query()->findOrFail($id);
    }

    /** @return array<string, mixed> */
    private function resource(EvidenceRecord $record): array
    {
        return ['id' => $record->getKey(), 'type' => 'genealogy-evidence', 'attributes' => [
            'name' => $record->name, 'kind' => $record->kind, 'repository' => $record->repository,
            'citation' => $record->citation, 'extract' => $record->extract, 'assertion' => $record->assertion,
            'proof_conclusion' => $record->proof_conclusion, 'confidence' => $record->confidence,
            'source_url' => $record->source_url, 'event_date' => $record->event_date?->toDateString(),
            'subject_person_id' => $record->subject_person_id, 'reviewed_at' => $record->reviewed_at?->toISOString(),
            'status' => $record->status, 'metadata' => $record->metadata,
        ]];
    }
}
