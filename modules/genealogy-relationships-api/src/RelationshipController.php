<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Liberu\Genealogy\Relationships\Actions\DeleteRelationship;
use Liberu\Genealogy\Relationships\Actions\UpdateRelationship;
use Liberu\Genealogy\Relationships\Models\Relationship;
use Liberu\Genealogy\Relationships\Queries\GraphValidator;

final class RelationshipController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'type' => ['sometimes', Rule::in(Relationship::TYPES)],
            'person_id' => ['sometimes', 'uuid', $this->personRule()],
            'confidence_min' => ['sometimes', 'integer', 'between:0,100'],
            'page[size]' => ['sometimes', 'integer', 'between:1,100'],
        ]);
        $perPage = min(max($request->integer('page[size]', 25), 1), 100);
        $relationships = Relationship::query()
            ->when(isset($values['type']), fn ($query) => $query->where('type', $values['type']))
            ->when(isset($values['person_id']), fn ($query) => $query->where(fn ($nested) => $nested->where('person_id', $values['person_id'])->orWhere('related_person_id', $values['person_id'])))
            ->when(isset($values['confidence_min']), fn ($query) => $query->where('confidence', '>=', $values['confidence_min']))
            ->latest()
            ->paginate($values['page[size]'] ?? $perPage);

        return response()->json([
            'data' => $relationships->getCollection()->map(fn (Relationship $relationship): array => $this->resource($relationship))->values()->all(),
            'meta' => ['current_page' => $relationships->currentPage(), 'per_page' => $relationships->perPage(), 'total' => $relationships->total()],
        ]);
    }

    public function validateGraph(Request $request, GraphValidator $validator): JsonResponse
    {
        $values = $request->validate([
            'person_id' => ['required', 'uuid', $this->personRule()],
            'related_person_id' => ['required', 'uuid', $this->personRule()],
            'type' => ['required', Rule::in(Relationship::TYPES)],
        ]);

        return response()->json(['data' => $validator->validate(
            $values['person_id'],
            $values['related_person_id'],
            $values['type'],
        )]);
    }

    public function store(Request $request, CreateRelationship $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'person_id' => ['required', 'uuid', $this->personRule(), Rule::notIn([$request->input('related_person_id')])],
            'related_person_id' => ['required', 'uuid', $this->personRule()],
            'type' => ['required', Rule::in(Relationship::TYPES)],
            'confidence' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function show(Relationship $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, Relationship $record, UpdateRelationship $update): JsonResponse
    {
        $values = $request->validate([
            'person_id' => ['sometimes', 'uuid', $this->personRule(), Rule::notIn([$request->input('related_person_id', $record->related_person_id)])],
            'related_person_id' => ['sometimes', 'uuid', $this->personRule()],
            'type' => ['sometimes', Rule::in(Relationship::TYPES)],
            'confidence' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ]);

        return response()->json(['data' => $this->resource($update->execute($record, $values))]);
    }

    public function destroy(Relationship $record, DeleteRelationship $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    private function personRule(): object
    {
        return Rule::exists('genealogy_people', 'id')
            ->where('team_id', app(TeamContext::class)->require());
    }

    /** @return array<string, mixed> */
    private function resource(Relationship $relationship): array
    {
        return ['id' => $relationship->getKey(), 'type' => 'genealogy-relationship', 'attributes' => [
            'person_id' => $relationship->person_id,
            'related_person_id' => $relationship->related_person_id,
            'type' => $relationship->type,
            'confidence' => $relationship->confidence,
            'metadata' => $relationship->metadata,
        ]];
    }
}
