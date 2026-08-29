<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Actions\CreatePersonAssociation;
use Liberu\Genealogy\People\Actions\DeletePerson;
use Liberu\Genealogy\People\Actions\DeletePersonAssociation;
use Liberu\Genealogy\People\Actions\RemovePersonAttribute;
use Liberu\Genealogy\People\Actions\ReviewMergeCandidate;
use Liberu\Genealogy\People\Actions\SetPersonLifeStatus;
use Liberu\Genealogy\People\Actions\UpdatePerson;
use Liberu\Genealogy\People\Actions\UpdatePersonAssociation;
use Liberu\Genealogy\People\Actions\UpdatePersonAttributes;
use Liberu\Genealogy\People\Models\MergeCandidate;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\People\Models\PersonAssociation;

final class PersonController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'public_only' => ['sometimes', 'boolean'],
            'include_living' => ['sometimes', 'boolean'],
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
        ]);

        $people = Person::query()
            ->with($this->includes($request))
            ->when(isset($values['search']), function ($query) use ($values): void {
                $search = $values['search'];
                $query->where(function ($nested) use ($search): void {
                    $nested->where('given_name', 'like', "%{$search}%")
                        ->orWhere('family_name', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%");
                });
            })
            ->when(($values['public_only'] ?? false) === true, fn ($query) => $query->where('is_public', true))
            ->when(($values['include_living'] ?? true) === false, fn ($query) => $query->deceased())
            ->latest()
            ->paginate($values['page']['size'] ?? 25);

        return response()->json([
            'data' => $people->getCollection()->map(fn (Person $person): array => $this->resource($person))->values()->all(),
            'meta' => ['current_page' => $people->currentPage(), 'per_page' => $people->perPage(), 'total' => $people->total()],
        ]);
    }

    public function store(Request $request, CreatePerson $createPerson): JsonResponse
    {
        $person = $createPerson->execute($this->validated($request, creating: true));

        return response()->json(['data' => $this->resource($person)], 201);
    }

    public function show(Person $person): JsonResponse
    {
        return response()->json(['data' => $this->resource($person)]);
    }

    public function update(Request $request, Person $person, UpdatePerson $updatePerson): JsonResponse
    {
        return response()->json(['data' => $this->resource($updatePerson->execute($person, $this->validated($request)))]);
    }

    public function destroy(Person $person, DeletePerson $delete): JsonResponse
    {
        $delete->execute($person);

        return response()->json(status: 204);
    }

    public function updateAttributes(Request $request, Person $person, UpdatePersonAttributes $update): JsonResponse
    {
        $values = $request->validate([
            'attributes' => ['required', 'array'],
            'replace' => ['sometimes', 'boolean'],
        ]);

        return response()->json(['data' => $this->resource($update->execute($person, $values['attributes'], $values['replace'] ?? false))]);
    }

    public function removeAttribute(Person $person, string $attribute, RemovePersonAttribute $remove): JsonResponse
    {
        return response()->json(['data' => $this->resource($remove->execute($person, $attribute))]);
    }

    public function setLifeStatus(Request $request, Person $person, SetPersonLifeStatus $setStatus): JsonResponse
    {
        $values = $request->validate([
            'status' => ['required', 'in:living,deceased'],
            'death_date' => ['nullable', 'date'],
        ]);

        return response()->json(['data' => $this->resource($setStatus->execute($person, $values['status'], $values['death_date'] ?? null))]);
    }

    public function storeAssociation(Request $request, Person $person, CreatePersonAssociation $create): JsonResponse
    {
        $values = $request->validate(['associated_person_id' => ['nullable', 'uuid'], 'associated_external_id' => ['nullable', 'string', 'max:255'], 'relationship' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'metadata' => ['nullable', 'array']]);
        $association = $create->execute(['person_id' => $person->getKey(), ...$values]);

        return response()->json(['data' => $this->associationResource($association)], 201);
    }

    public function updateAssociation(Request $request, Person $person, PersonAssociation $association, UpdatePersonAssociation $update): JsonResponse
    {
        abort_unless((string) $association->person_id === (string) $person->getKey(), 404);
        $values = $request->validate(['associated_person_id' => ['nullable', 'uuid'], 'associated_external_id' => ['nullable', 'string', 'max:255'], 'relationship' => ['sometimes', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'metadata' => ['nullable', 'array']]);

        return response()->json(['data' => $this->associationResource($update->execute($association, $values))]);
    }

    public function destroyAssociation(Person $person, PersonAssociation $association, DeletePersonAssociation $delete): JsonResponse
    {
        abort_unless((string) $association->person_id === (string) $person->getKey(), 404);
        $delete->execute($association);

        return response()->json(status: 204);
    }

    public function reviewMergeCandidate(
        Request $request,
        string $person,
        string $candidate,
        ReviewMergeCandidate $review,
    ): JsonResponse {
        $values = $request->validate([
            'status' => ['required', 'in:accepted,rejected'],
            'reason' => ['nullable', 'string', 'max:10000'],
        ]);
        $record = MergeCandidate::query()
            ->whereKey($candidate)
            ->where('person_id', $person)
            ->firstOrFail();
        $reviewed = $review->execute($record, $values['status'], $values['reason'] ?? null);

        return response()->json([
            'data' => [
                'id' => (string) $reviewed->getKey(),
                'type' => 'genealogy-merge-candidate',
                'attributes' => $reviewed->only(['person_id', 'candidate_person_id', 'status', 'score', 'reason', 'reviewed_at']),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $creating = false): array
    {
        return $request->validate([
            'given_name' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'family_name' => ['nullable', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'sex' => ['nullable', 'string', 'size:1', 'in:M,F,U,X'],
            'aliases' => ['nullable', 'array'],
            'attributes' => ['nullable', 'array'],
            'birth_date' => ['nullable', 'date'],
            'death_date' => ['nullable', 'date', 'after_or_equal:birth_date'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'death_place' => ['nullable', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]);
    }

    /** @return array<string, mixed> */
    private function resource(Person $person): array
    {
        return [
            'id' => $person->getKey(),
            'type' => 'genealogy-person',
            'attributes' => [
                'given_name' => $person->given_name,
                'family_name' => $person->family_name,
                'display_name' => $person->display_name,
                'sex' => $person->sex,
                'aliases' => $person->aliases,
                'attributes' => $person->attributes,
                'birth_date' => $person->birth_date?->toDateString(),
                'death_date' => $person->death_date?->toDateString(),
                'birth_place' => $person->birth_place,
                'death_place' => $person->death_place,
                'is_public' => $person->is_public,
                'is_living' => $person->isLiving(),
                'life_status' => $person->isLiving() ? 'living' : 'deceased',
                'metadata' => $person->metadata,
            ],
            'relationships' => [
                'names' => $person->relationLoaded('names') ? $person->names->map(fn ($name): array => [
                    'id' => (string) $name->getKey(),
                    'type' => 'genealogy-person-name',
                    'attributes' => $name->only(['type', 'given_name', 'family_name', 'prefix', 'suffix', 'is_preferred']),
                ])->values()->all() : [],
                'identities' => $person->relationLoaded('identities') ? $person->identities->map(fn ($identity): array => [
                    'id' => (string) $identity->getKey(),
                    'type' => 'genealogy-person-identity',
                    'attributes' => $identity->only(['type', 'value', 'label', 'is_verified']),
                ])->values()->all() : [],
                'life_events' => $person->relationLoaded('lifeEvents') ? $person->lifeEvents->map(fn ($event): array => [
                    'id' => (string) $event->getKey(),
                    'type' => 'genealogy-person-life-event',
                    'attributes' => $event->only(['type', 'date', 'place', 'description', 'metadata']),
                ])->values()->all() : [],
                'associations' => $person->relationLoaded('associations') ? $person->associations->map(fn (PersonAssociation $association): array => $this->associationResource($association))->values()->all() : [],
            ],
        ];
    }

    private function associationResource(PersonAssociation $association): array
    {
        return ['id' => (string) $association->getKey(), 'type' => 'genealogy-person-association', 'attributes' => $association->only(['person_id', 'associated_person_id', 'associated_external_id', 'relationship', 'description', 'metadata']), 'resolved' => $association->isResolved()];
    }

    /** @return list<string> */
    private function includes(Request $request): array
    {
        $allowed = ['names', 'identities', 'lifeEvents', 'associations'];
        $requested = array_filter(explode(',', (string) $request->query('include', '')));

        return array_values(array_intersect($requested, $allowed));
    }
}
