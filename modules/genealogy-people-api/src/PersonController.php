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
    public function index(): JsonResponse
    {
        return response()->json(['data' => Person::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreatePerson $createPerson): JsonResponse
    {
        $person = $createPerson->execute($this->validated($request));

        return response()->json(['data' => $person], 201);
    }

    public function show(Person $person): JsonResponse
    {
        return response()->json(['data' => $person]);
    }

    public function update(Request $request, Person $person, UpdatePerson $updatePerson): JsonResponse
    {
        return response()->json(['data' => $updatePerson->execute($person, $this->validated($request))]);
    }

    public function destroy(Person $person): JsonResponse
    {
        $person->delete();

        return response()->json(status: 204);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'given_name' => ['required', 'string', 'max:255'],
            'family_name' => ['nullable', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'death_date' => ['nullable', 'date', 'after_or_equal:birth_date'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'death_place' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);
    }
}
