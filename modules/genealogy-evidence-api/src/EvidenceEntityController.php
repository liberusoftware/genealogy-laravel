<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\Evidence\Actions\CreateCitationLink;
use Liberu\Genealogy\Evidence\Actions\CreateEvidenceEntity;
use Liberu\Genealogy\Evidence\Actions\DeleteCitationLink;
use Liberu\Genealogy\Evidence\Actions\DeleteEvidenceEntity;
use Liberu\Genealogy\Evidence\Actions\UpdateCitationLink;
use Liberu\Genealogy\Evidence\Actions\UpdateEvidenceEntity;
use Liberu\Genealogy\Evidence\Models\Assertion;
use Liberu\Genealogy\Evidence\Models\Citation;
use Liberu\Genealogy\Evidence\Models\CitationLink;
use Liberu\Genealogy\Evidence\Models\Extract;
use Liberu\Genealogy\Evidence\Models\ProofConclusion;
use Liberu\Genealogy\Evidence\Models\Repository;
use Liberu\Genealogy\Evidence\Models\Source;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class EvidenceEntityController
{
    public function index(Request $request, string $entity): JsonResponse
    {
        $model = $this->model($entity);
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ]);
        $perPage = $values['page']['size'] ?? $values['per_page'] ?? 25;
        $records = $model::query()->latest()->paginate($perPage);

        return response()->json(['data' => $records->getCollection()->map(fn ($record): array => $this->resource($record))->values()->all(), 'meta' => [
            'current_page' => $records->currentPage(), 'per_page' => $records->perPage(), 'total' => $records->total(),
        ]]);
    }

    public function store(Request $request, string $entity, CreateEvidenceEntity $create): JsonResponse
    {
        $record = $create->execute($this->model($entity), $this->validated($request, $entity));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function show(string $entity, string $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($this->model($entity)::query()->findOrFail($record))]);
    }

    public function update(Request $request, string $entity, string $record, UpdateEvidenceEntity $update): JsonResponse
    {
        $model = $this->model($entity);
        $item = $model::query()->findOrFail($record);

        return response()->json(['data' => $this->resource($update->execute($item, $this->validated($request, $entity, true)))]);
    }

    public function destroy(string $entity, string $record, DeleteEvidenceEntity $delete): JsonResponse
    {
        $delete->execute($this->model($entity)::query()->findOrFail($record));

        return response()->json(status: 204);
    }

    public function citationLinks(string $citation): JsonResponse
    {
        $this->citation($citation);

        return response()->json(['data' => CitationLink::query()->where('citation_id', $citation)->latest()->get()->map(fn (CitationLink $link): array => $this->citationLinkResource($link))->values()->all()]);
    }

    public function storeCitationLink(Request $request, string $citation, CreateCitationLink $create): JsonResponse
    {
        $this->citation($citation);
        $values = $request->validate([
            'subject_person_id' => ['required', 'uuid'],
            'group' => ['sometimes', 'in:'.implode(',', CitationLink::GROUPS)],
            'page' => ['nullable', 'string', 'max:255'],
            'quality' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);
        $link = $create->execute(['citation_id' => $citation, ...$values]);

        return response()->json(['data' => $this->citationLinkResource($link)], 201);
    }

    public function updateCitationLink(Request $request, string $citation, string $link, UpdateCitationLink $update): JsonResponse
    {
        $this->citation($citation);
        $record = CitationLink::query()->where('citation_id', $citation)->findOrFail($link);
        $values = $request->validate([
            'group' => ['sometimes', 'in:'.implode(',', CitationLink::GROUPS)],
            'page' => ['nullable', 'string', 'max:255'],
            'quality' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        return response()->json(['data' => $this->citationLinkResource($update->execute($record, $values))]);
    }

    public function destroyCitationLink(string $citation, string $link, DeleteCitationLink $delete): JsonResponse
    {
        $this->citation($citation);
        $delete->execute(CitationLink::query()->where('citation_id', $citation)->findOrFail($link));

        return response()->json(status: 204);
    }

    /** @return class-string<Model> */
    private function model(string $entity): string
    {
        return match ($entity) {
            'sources' => Source::class,
            'repositories' => Repository::class,
            'citations' => Citation::class,
            'extracts' => Extract::class,
            'assertions' => Assertion::class,
            'proof-conclusions' => ProofConclusion::class,
            default => abort(404),
        };
    }

    private function citation(string $id): Citation
    {
        return Citation::query()->where('team_id', app(TeamContext::class)->require())->findOrFail($id);
    }

    /** @return array<string, mixed> */
    private function citationLinkResource(CitationLink $link): array
    {
        return ['id' => (string) $link->getKey(), 'type' => 'genealogy-evidence-citation-link', 'attributes' => $link->only(['citation_id', 'subject_person_id', 'group', 'page', 'quality', 'text', 'metadata']), 'quality_label' => $link->qualityLabel()];
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, string $entity, bool $update = false): array
    {
        $required = $update ? 'sometimes' : 'required';
        $rules = match ($entity) {
            'sources' => ['name' => [$required, 'string', 'max:255'], 'description' => ['nullable', 'string'], 'url' => ['nullable', 'url', 'max:2048'], 'record_type' => ['nullable', 'string', 'max:100'], 'is_active' => ['sometimes', 'boolean'], 'metadata' => ['nullable', 'array']],
            'repositories' => ['name' => [$required, 'string', 'max:255'], 'source_id' => ['nullable', 'uuid', Rule::exists('genealogy_evidence_sources', 'id')], 'description' => ['nullable', 'string'], 'address' => ['nullable', 'string'], 'url' => ['nullable', 'url', 'max:2048'], 'email' => ['nullable', 'email'], 'is_active' => ['sometimes', 'boolean'], 'metadata' => ['nullable', 'array']],
            'citations' => ['source_id' => [$required, 'uuid', Rule::exists('genealogy_evidence_sources', 'id')], 'repository_id' => ['nullable', 'uuid', Rule::exists('genealogy_evidence_repositories', 'id')], 'title' => ['nullable', 'string', 'max:255'], 'volume' => ['nullable', 'string', 'max:255'], 'page' => ['nullable', 'string', 'max:255'], 'text' => ['nullable', 'string'], 'confidence' => ['sometimes', 'integer', 'between:0,100'], 'event_date' => ['nullable', 'date'], 'metadata' => ['nullable', 'array']],
            'extracts' => ['citation_id' => [$required, 'uuid', Rule::exists('genealogy_evidence_citations', 'id')], 'content' => [$required, 'string'], 'transcription' => ['nullable', 'string'], 'page' => ['nullable', 'string', 'max:255'], 'metadata' => ['nullable', 'array']],
            'assertions' => ['statement' => [$required, 'string'], 'subject_person_id' => ['nullable', 'uuid', Rule::exists('genealogy_people', 'id')], 'citation_id' => ['nullable', 'uuid', Rule::exists('genealogy_evidence_citations', 'id')], 'extract_id' => ['nullable', 'uuid', Rule::exists('genealogy_evidence_extracts', 'id')], 'confidence' => ['sometimes', 'integer', 'between:0,100'], 'status' => ['sometimes', 'string', 'max:50'], 'metadata' => ['nullable', 'array']],
            default => ['assertion_id' => [$required, 'uuid', Rule::exists('genealogy_evidence_assertions', 'id')], 'conclusion' => [$required, 'string'], 'confidence' => ['sometimes', 'integer', 'between:0,100'], 'status' => ['sometimes', 'string', 'max:50'], 'metadata' => ['nullable', 'array']],
        };

        return $request->validate($rules);
    }

    /** @return array<string, mixed> */
    private function resource(object $record): array
    {
        return ['id' => $record->getKey(), 'type' => 'genealogy-evidence-'.$record->getTable(), 'attributes' => $record->toArray()];
    }
}
