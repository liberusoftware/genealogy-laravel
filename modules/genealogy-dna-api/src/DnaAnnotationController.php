<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\Dna\Actions\CreateDnaNote;
use Liberu\Genealogy\Dna\Actions\CreateDnaRelationship;
use Liberu\Genealogy\Dna\Actions\DeleteDnaNote;
use Liberu\Genealogy\Dna\Actions\DeleteDnaRelationship;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\Dna\Models\DnaNote;
use Liberu\Genealogy\Dna\Models\DnaRelationship;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DnaAnnotationController
{
    public function notes(Request $request): JsonResponse
    {
        $values = $request->validate(['page' => ['sometimes', 'array'], 'page.size' => ['sometimes', 'integer', 'between:1,100'], 'noteable_type' => ['sometimes', 'in:'.implode(',', [DnaKit::class, DnaMatch::class])], 'noteable_id' => ['sometimes', 'uuid']]);
        if (isset($values['noteable_type'], $values['noteable_id'])) {
            $this->validateReference($values['noteable_type'], $values['noteable_id']);
        }
        $records = DnaNote::query()->when(isset($values['noteable_type']), fn ($query) => $query->where('noteable_type', $values['noteable_type']))->when(isset($values['noteable_id']), fn ($query) => $query->where('noteable_id', $values['noteable_id']))->latest()->paginate($values['page']['size'] ?? 25);

        return $this->collection($records->getCollection()->map(fn (DnaNote $record): array => $this->note($record))->all(), $records);
    }

    public function createNote(Request $request, CreateDnaNote $create): JsonResponse
    {
        $values = $request->validate(['noteable_type' => ['required', 'in:'.implode(',', [DnaKit::class, DnaMatch::class])], 'noteable_id' => ['required', 'uuid'], 'body' => ['required', 'string', 'max:50000']]);
        $this->validateReference($values['noteable_type'], $values['noteable_id']);
        $record = $create->execute($values);

        return response()->json(['data' => $this->note($record)], 201);
    }

    public function deleteNote(DnaNote $record, DeleteDnaNote $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    public function relationships(Request $request): JsonResponse
    {
        $values = $request->validate(['page' => ['sometimes', 'array'], 'page.size' => ['sometimes', 'integer', 'between:1,100'], 'match_id' => ['sometimes', 'uuid', Rule::exists('genealogy_dna_matches', 'id')->where('team_id', app(TeamContext::class)->require())], 'person_id' => ['sometimes', 'uuid', Rule::exists('genealogy_people', 'id')->where('team_id', app(TeamContext::class)->require())]]);
        $records = DnaRelationship::query()->when(isset($values['match_id']), fn ($query) => $query->where('match_id', $values['match_id']))->when(isset($values['person_id']), fn ($query) => $query->where('person_id', $values['person_id']))->latest()->paginate($values['page']['size'] ?? 25);

        return $this->collection($records->getCollection()->map(fn (DnaRelationship $record): array => $this->relationship($record))->all(), $records);
    }

    public function createRelationship(Request $request, CreateDnaRelationship $create): JsonResponse
    {
        $record = $create->execute($request->validate(['match_id' => ['required', 'uuid', Rule::exists('genealogy_dna_matches', 'id')->where('team_id', app(TeamContext::class)->require())], 'person_id' => ['required', 'uuid', Rule::exists('genealogy_people', 'id')->where('team_id', app(TeamContext::class)->require())], 'relationship_type' => ['required', 'string', 'max:100'], 'confidence' => ['nullable', 'integer', 'between:0,100'], 'status' => ['sometimes', 'in:proposed,confirmed,rejected'], 'rationale' => ['nullable', 'string', 'max:10000'], 'metadata' => ['nullable', 'array']]));

        return response()->json(['data' => $this->relationship($record)], 201);
    }

    public function deleteRelationship(DnaRelationship $record, DeleteDnaRelationship $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    private function collection(array $data, mixed $records): JsonResponse
    {
        return response()->json(['data' => array_values($data), 'meta' => ['current_page' => $records->currentPage(), 'per_page' => $records->perPage(), 'total' => $records->total()]]);
    }

    private function validateReference(string $type, string $id): void
    {
        $table = $type === DnaKit::class ? 'genealogy_dna_kits' : 'genealogy_dna_matches';
        request()->validate(['noteable_id' => [Rule::exists($table, 'id')->where('team_id', app(TeamContext::class)->require())]], ['noteable_id.exists' => 'The note target is not available in the active team.']);
    }

    /** @return array<string, mixed> */
    private function note(DnaNote $record): array
    {
        return ['id' => $record->getKey(), 'type' => 'genealogy-dna-note', 'attributes' => ['noteable_type' => $record->noteable_type, 'noteable_id' => $record->noteable_id, 'body' => $record->body, 'created_at' => $record->created_at?->toISOString(), 'updated_at' => $record->updated_at?->toISOString()]];
    }

    /** @return array<string, mixed> */
    private function relationship(DnaRelationship $record): array
    {
        return ['id' => $record->getKey(), 'type' => 'genealogy-dna-relationship', 'attributes' => ['match_id' => $record->match_id, 'person_id' => $record->person_id, 'relationship_type' => $record->relationship_type, 'confidence' => $record->confidence, 'status' => $record->status, 'rationale' => $record->rationale, 'metadata' => $record->metadata, 'created_at' => $record->created_at?->toISOString(), 'updated_at' => $record->updated_at?->toISOString()]];
    }
}
