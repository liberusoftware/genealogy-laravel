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
        $people = Person::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = (string) $request->string('search');
                $query->where(function ($nested) use ($search): void {
                    $nested->where('given_name', 'like', "%{$search}%")
                        ->orWhere('family_name', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(min((int) $request->input('per_page', 25), 100));

        return response()->json(['data' => $people]);
    }

    public function store(Request $request, CreatePerson $createPerson): JsonResponse
    {
        $person = $createPerson->execute($this->validated($request, creating: true));

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
}
