<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\Places\Actions\CreatePlace;
use Liberu\Genealogy\Places\Actions\CreatePlaceName;
use Liberu\Genealogy\Places\Actions\DeletePlace;
use Liberu\Genealogy\Places\Actions\DeletePlaceName;
use Liberu\Genealogy\Places\Actions\UpdatePlace;
use Liberu\Genealogy\Places\Actions\UpdatePlaceName;
use Liberu\Genealogy\Places\Models\Place;
use Liberu\Genealogy\Places\Models\PlaceName;
use Liberu\Genealogy\Places\Queries\PlaceHierarchy;

final class PlaceController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'status' => ['sometimes', Rule::in(Place::STATUSES)],
            'jurisdiction' => ['sometimes', 'string', 'max:255'],
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
        ]);
        $places = Place::query()
            ->when(isset($values['status']), fn ($query) => $query->where('status', $values['status']))
            ->when(isset($values['jurisdiction']), fn ($query) => $query->where('jurisdiction', $values['jurisdiction']))
            ->latest()
            ->paginate($values['page']['size'] ?? 25);

        return response()->json([
            'data' => $places->getCollection()->map(fn (Place $place): array => $this->resource($place))->values()->all(),
            'meta' => ['current_page' => $places->currentPage(), 'per_page' => $places->perPage(), 'total' => $places->total()],
        ]);
    }

    public function hierarchy(PlaceHierarchy $hierarchy): JsonResponse
    {
        return response()->json(['data' => $hierarchy->execute()]);
    }

    public function store(Request $request, CreatePlace $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'uuid'],
            'historical_names' => ['nullable', 'array'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'jurisdiction' => ['nullable', 'string', 'max:255'],
            'is_current' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(Place::STATUSES)],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function show(Place $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, Place $record, UpdatePlace $update): JsonResponse
    {
        $place = $update->execute($record, $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'parent_id' => ['sometimes', 'nullable', 'uuid'],
            'historical_names' => ['nullable', 'array'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'jurisdiction' => ['nullable', 'string', 'max:255'],
            'is_current' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(Place::STATUSES)],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($place)]);
    }

    public function destroy(Place $record, DeletePlace $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    public function names(string $record): JsonResponse
    {
        $place = $this->place($record);

        return response()->json(['data' => $place->names()->latest()->get()->map(fn (PlaceName $name): array => $this->nameResource($name))->values()]);
    }

    public function storeName(Request $request, string $record, CreatePlaceName $create): JsonResponse
    {
        $this->place($record);
        $name = $create->execute(array_merge($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:12'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'metadata' => ['nullable', 'array'],
        ]), ['place_id' => $record]));

        return response()->json(['data' => $this->nameResource($name)], 201);
    }

    public function updateName(Request $request, string $record, string $name, UpdatePlaceName $update): JsonResponse
    {
        $this->place($record);
        $placeName = $this->name($record, $name);
        $placeName = $update->execute($placeName, $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:12'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->nameResource($placeName)]);
    }

    public function destroyName(string $record, string $name, DeletePlaceName $delete): JsonResponse
    {
        $delete->execute($this->name($record, $name));

        return response()->json(status: 204);
    }

    private function place(string $id): Place
    {
        return Place::query()->whereKey($id)->firstOrFail();
    }

    private function name(string $placeId, string $nameId): PlaceName
    {
        return PlaceName::query()->where('place_id', $placeId)->whereKey($nameId)->firstOrFail();
    }

    /** @return array<string, mixed> */
    private function nameResource(PlaceName $name): array
    {
        return ['id' => $name->getKey(), 'type' => 'genealogy-place-name', 'attributes' => [
            'place_id' => $name->place_id, 'name' => $name->name, 'type' => $name->type,
            'locale' => $name->locale, 'valid_from' => $name->valid_from?->toDateString(),
            'valid_to' => $name->valid_to?->toDateString(), 'metadata' => $name->metadata,
        ]];
    }

    /** @return array<string, mixed> */
    private function resource(Place $place): array
    {
        return ['id' => $place->getKey(), 'type' => 'genealogy-place', 'attributes' => [
            'name' => $place->name,
            'parent_id' => $place->parent_id,
            'historical_names' => $place->historical_names,
            'latitude' => $place->latitude,
            'longitude' => $place->longitude,
            'has_coordinates' => $place->hasCoordinates(),
            'map_url' => $place->mapUrl(),
            'jurisdiction' => $place->jurisdiction,
            'is_current' => $place->is_current,
            'status' => $place->status,
            'metadata' => $place->metadata,
            'created_at' => $place->created_at?->toISOString(),
            'updated_at' => $place->updated_at?->toISOString(),
        ]];
    }
}
