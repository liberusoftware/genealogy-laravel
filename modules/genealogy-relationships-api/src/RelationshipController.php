<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Liberu\Genealogy\Relationships\Models\Relationship;
use Liberu\Genealogy\Relationships\Queries\GraphValidator;

final class RelationshipController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Relationship::query()->latest()->paginate()]);
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

        return response()->json(['data' => $record], 201);
    }

    public function show(Relationship $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, Relationship $record): JsonResponse
    {
        $record->update($request->validate([
            'person_id' => ['sometimes', 'uuid', $this->personRule(), Rule::notIn([$request->input('related_person_id', $record->related_person_id)])],
            'related_person_id' => ['sometimes', 'uuid', $this->personRule()],
            'type' => ['sometimes', Rule::in(Relationship::TYPES)],
            'confidence' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(Relationship $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }

    private function personRule(): object
    {
        return Rule::exists('genealogy_people', 'id')
            ->where('team_id', app(TeamContext::class)->require());
    }
}
