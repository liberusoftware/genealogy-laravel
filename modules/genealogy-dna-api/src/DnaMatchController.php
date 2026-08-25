<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Dna\Actions\CreateDnaMatch;
use Liberu\Genealogy\Dna\Actions\CreateDnaSegment;
use Liberu\Genealogy\Dna\Models\DnaMatch;

final class DnaMatchController
{
    public function index(Request $request): JsonResponse
    {
        $matches = DnaMatch::query()->when(! $request->boolean('include_private'), fn ($query) => $query->where('is_private', false))->latest()->paginate(min(max($request->integer('page[size]', 25), 1), 100));

        return response()->json(['data' => $matches->through(fn (DnaMatch $match): array => $this->resource($match)), 'meta' => ['current_page' => $matches->currentPage(), 'per_page' => $matches->perPage(), 'total' => $matches->total()]]);
    }

    public function store(Request $request, CreateDnaMatch $create): JsonResponse
    {
        $values = $request->validate(['kit_id' => ['required', 'uuid'], 'external_id' => ['required', 'string', 'max:255'], 'display_name' => ['nullable', 'string', 'max:255'], 'predicted_relationship' => ['nullable', 'string', 'max:100'], 'confidence' => ['nullable', 'integer', 'between:0,100'], 'total_cm' => ['nullable', 'numeric', 'min:0'], 'shared_segments' => ['nullable', 'integer', 'min:0'], 'status' => ['sometimes', 'string', 'max:50'], 'is_private' => ['sometimes', 'boolean'], 'notes' => ['nullable', 'string'], 'metadata' => ['nullable', 'array']]);
        $match = $create->execute($values);

        return response()->json(['data' => $this->resource($match)], 201);
    }

    public function show(DnaMatch $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record->load('segments'))]);
    }

    public function update(Request $request, DnaMatch $record): JsonResponse
    {
        $record->update($request->validate(['display_name' => ['sometimes', 'nullable', 'string', 'max:255'], 'predicted_relationship' => ['sometimes', 'nullable', 'string', 'max:100'], 'confidence' => ['sometimes', 'nullable', 'integer', 'between:0,100'], 'status' => ['sometimes', 'string', 'max:50'], 'is_private' => ['sometimes', 'boolean'], 'notes' => ['sometimes', 'nullable', 'string'], 'metadata' => ['sometimes', 'nullable', 'array']]));

        return response()->json(['data' => $this->resource($record->refresh())]);
    }

    public function destroy(DnaMatch $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }

    public function segment(Request $request, DnaMatch $record, CreateDnaSegment $create): JsonResponse
    {
        $values = $request->validate(['chromosome' => ['required', 'integer', 'between:1,99'], 'start_position' => ['required', 'integer', 'min:0'], 'end_position' => ['required', 'integer', 'gt:start_position'], 'centimorgans' => ['nullable', 'numeric', 'min:0'], 'snps' => ['nullable', 'integer', 'min:0'], 'side' => ['nullable', 'string', 'max:50'], 'metadata' => ['nullable', 'array'], 'match_id' => ['prohibited']]);
        $segment = $create->execute([...$values, 'match_id' => $record->getKey()]);

        return response()->json(['data' => $segment], 201);
    }

    /** @return array<string, mixed> */
    private function resource(DnaMatch $match): array
    {
        return ['id' => $match->getKey(), 'type' => 'genealogy-dna-match', 'attributes' => ['kit_id' => $match->kit_id, 'external_id' => $match->external_id, 'display_name' => $match->display_name, 'predicted_relationship' => $match->predicted_relationship, 'confidence' => $match->confidence, 'total_cm' => $match->total_cm, 'shared_segments' => $match->shared_segments, 'status' => $match->status, 'is_private' => $match->is_private, 'notes' => $match->notes, 'metadata' => $match->metadata], 'relationships' => ['segments' => $match->relationLoaded('segments') ? $match->segments->map(fn ($segment): array => ['id' => $segment->getKey(), 'chromosome' => $segment->chromosome, 'start_position' => $segment->start_position, 'end_position' => $segment->end_position, 'centimorgans' => $segment->centimorgans])->all() : []]];
    }
}
