<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Actions\UpdatePerson;
use Liberu\Genealogy\People\Models\Person;

final class PersonController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'public_only' => ['sometimes', 'boolean'],
            'include_living' => ['sometimes', 'boolean'],
            'page[size]' => ['sometimes', 'integer', 'between:1,100'],
        ]);

        $people = Person::query()
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
            ->paginate($values['page[size]'] ?? 25);

        return response()->json([
            'data' => $people->through(fn (Person $person): array => $this->resource($person)),
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

    public function destroy(Person $person): JsonResponse
    {
        $person->delete();

        return response()->json(status: 204);
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
                'metadata' => $person->metadata,
            ],
        ];
    }
}
