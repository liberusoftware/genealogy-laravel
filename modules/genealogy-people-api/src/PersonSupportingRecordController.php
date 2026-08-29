<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Api;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreateMergeCandidate;
use Liberu\Genealogy\People\Actions\CreatePersonIdentity;
use Liberu\Genealogy\People\Actions\CreatePersonLifeEvent;
use Liberu\Genealogy\People\Actions\CreatePersonName;
use Liberu\Genealogy\People\Actions\DeletePersonSupportingRecord;
use Liberu\Genealogy\People\Actions\UpdatePersonSupportingRecord;
use Liberu\Genealogy\People\Models\Person;

final class PersonSupportingRecordController
{
    public function index(Person $person, string $supporting): JsonResponse
    {
        $records = $this->relation($person, $supporting)->latest()->paginate(25);

        return response()->json(['data' => $records->getCollection()->map(fn ($record): array => $this->resource($record, $supporting))->values()->all(), 'meta' => [
            'current_page' => $records->currentPage(), 'per_page' => $records->perPage(), 'total' => $records->total(),
        ]]);
    }

    public function store(
        Request $request,
        Person $person,
        string $supporting,
        CreatePersonName $createName,
        CreatePersonIdentity $createIdentity,
        CreatePersonLifeEvent $createLifeEvent,
        CreateMergeCandidate $createCandidate,
    ): JsonResponse {
        $values = $this->validated($request, $supporting);
        $record = match ($supporting) {
            'names' => $createName->execute($values + ['person_id' => $person->getKey()]),
            'identities' => $createIdentity->execute($values + ['person_id' => $person->getKey()]),
            'life-events' => $createLifeEvent->execute($values + ['person_id' => $person->getKey()]),
            'merge-candidates' => $createCandidate->execute($values + ['person_id' => $person->getKey()]),
            default => abort(404),
        };

        return response()->json(['data' => $this->resource($record, $supporting)], 201);
    }

    public function update(Request $request, Person $person, string $supporting, string $record, UpdatePersonSupportingRecord $update): JsonResponse
    {
        $item = $this->relation($person, $supporting)->whereKey($record)->firstOrFail();

        return response()->json(['data' => $this->resource($update->execute($item, $this->validated($request, $supporting, true)), $supporting)]);
    }

    public function destroy(Person $person, string $supporting, string $record, DeletePersonSupportingRecord $delete): JsonResponse
    {
        $item = $this->relation($person, $supporting)->whereKey($record)->firstOrFail();
        $delete->execute($item);

        return response()->json(status: 204);
    }

    /** @return HasMany */
    private function relation(Person $person, string $supporting): mixed
    {
        return match ($supporting) {
            'names' => $person->names(),
            'identities' => $person->identities(),
            'life-events' => $person->lifeEvents(),
            'merge-candidates' => $person->mergeCandidates(),
            default => abort(404),
        };
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, string $supporting, bool $update = false): array
    {
        $required = $update ? 'sometimes' : 'required';

        return $request->validate(match ($supporting) {
            'names' => ['type' => ['sometimes', 'string', 'max:50'], 'given_name' => [$required, 'nullable', 'string', 'max:255'], 'family_name' => [$required, 'nullable', 'string', 'max:255'], 'prefix' => ['nullable', 'string', 'max:50'], 'suffix' => ['nullable', 'string', 'max:50'], 'is_preferred' => ['sometimes', 'boolean'], 'metadata' => ['nullable', 'array']],
            'identities' => ['type' => [$required, 'string', 'max:100'], 'value' => [$required, 'string', 'max:500'], 'label' => ['nullable', 'string', 'max:255'], 'is_verified' => ['sometimes', 'boolean'], 'metadata' => ['nullable', 'array']],
            'life-events' => ['type' => [$required, 'string', 'max:100'], 'date' => ['nullable', 'date'], 'place' => ['nullable', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'metadata' => ['nullable', 'array']],
            default => ['candidate_person_id' => [$required, 'uuid', Rule::exists('genealogy_people', 'id')->where('team_id', app(TeamContext::class)->require())], 'status' => ['sometimes', 'in:pending,accepted,rejected'], 'score' => ['nullable', 'numeric', 'between:0,1'], 'reason' => ['nullable', 'string'], 'metadata' => ['nullable', 'array']],
        });
    }

    /** @return array<string, mixed> */
    private function resource(object $record, string $supporting): array
    {
        return ['id' => $record->getKey(), 'type' => 'genealogy-person-'.$supporting, 'attributes' => $record->only(array_values(array_diff($record->getFillable(), ['team_id', 'person_id'])))];
    }
}
