<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Dna\Actions\CreateDnaProvider;
use Liberu\Genealogy\Dna\Actions\DeleteDnaProvider;
use Liberu\Genealogy\Dna\Actions\UpdateDnaProvider;
use Liberu\Genealogy\Dna\Models\DnaProvider;

final class DnaProviderController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
            'status' => ['sometimes', 'in:'.implode(',', DnaProvider::STATUSES)],
        ]);
        $providers = DnaProvider::query()
            ->when(isset($values['status']), fn ($query) => $query->where('status', $values['status']))
            ->withCount('kits')
            ->latest()
            ->paginate($values['page']['size'] ?? 25);

        return response()->json([
            'data' => $providers->getCollection()->map(fn (DnaProvider $provider): array => $this->resource($provider))->values()->all(),
            'meta' => ['current_page' => $providers->currentPage(), 'per_page' => $providers->perPage(), 'total' => $providers->total()],
        ]);
    }

    public function store(Request $request, CreateDnaProvider $create): JsonResponse
    {
        $provider = $create->execute($request->validate($this->rules()));

        return response()->json(['data' => $this->resource($provider)], 201);
    }

    public function show(DnaProvider $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record->loadCount('kits'))]);
    }

    public function update(Request $request, DnaProvider $record, UpdateDnaProvider $update): JsonResponse
    {
        $provider = $update->execute($record, $request->validate($this->rules(true)));

        return response()->json(['data' => $this->resource($provider->loadCount('kits'))]);
    }

    public function destroy(DnaProvider $record, DeleteDnaProvider $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    /** @return array<string, array<int, string>> */
    private function rules(bool $update = false): array
    {
        $sometimes = $update ? 'sometimes' : 'required';

        return [
            'name' => [$sometimes, 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:'.implode(',', DnaProvider::STATUSES)],
            'website' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    /** @return array<string, mixed> */
    private function resource(DnaProvider $provider): array
    {
        return ['id' => $provider->getKey(), 'type' => 'genealogy-dna-provider', 'attributes' => [
            'name' => $provider->name,
            'slug' => $provider->slug,
            'status' => $provider->status,
            'website' => $provider->website,
            'metadata' => $provider->metadata,
            'kits_count' => $provider->kits_count ?? null,
            'created_at' => $provider->created_at?->toISOString(),
            'updated_at' => $provider->updated_at?->toISOString(),
        ]];
    }
}
