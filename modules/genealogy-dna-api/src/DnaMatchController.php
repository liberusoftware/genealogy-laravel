<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\Dna\Actions\CreateDnaMatch;
use Liberu\Genealogy\Dna\Actions\CreateDnaSegment;
use Liberu\Genealogy\Dna\Actions\DeleteDnaMatch;
use Liberu\Genealogy\Dna\Actions\DeleteDnaSegment;
use Liberu\Genealogy\Dna\Actions\PersistDnaComparison;
use Liberu\Genealogy\Dna\Actions\UpdateDnaMatch;
use Liberu\Genealogy\Dna\Actions\UpdateDnaSegment;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\Dna\Models\DnaSegment;
use Liberu\Genealogy\Dna\Services\AnalyzeDnaMatch;
use Liberu\Genealogy\Dna\Services\CompareDnaKits;
use Liberu\Genealogy\Dna\Services\TriangulateDna;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DnaMatchController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
        ]);
        $matches = DnaMatch::query()->when(! $request->boolean('include_private'), fn ($query) => $query->where('is_private', false))->latest()->paginate($values['page']['size'] ?? 25);

        return response()->json(['data' => $matches->getCollection()->map(fn (DnaMatch $match): array => $this->resource($match))->values()->all(), 'meta' => ['current_page' => $matches->currentPage(), 'per_page' => $matches->perPage(), 'total' => $matches->total()]]);
    }

    public function store(Request $request, CreateDnaMatch $create): JsonResponse
    {
        $values = $request->validate(['kit_id' => ['required', 'uuid', Rule::exists('genealogy_dna_kits', 'id')->where('team_id', app(TeamContext::class)->require())], 'external_id' => ['required', 'string', 'max:255'], 'display_name' => ['nullable', 'string', 'max:255'], 'predicted_relationship' => ['nullable', 'string', 'max:100'], 'confidence' => ['nullable', 'integer', 'between:0,100'], 'total_cm' => ['nullable', 'numeric', 'min:0'], 'shared_segments' => ['nullable', 'integer', 'min:0'], 'status' => ['sometimes', 'string', 'max:50'], 'is_private' => ['sometimes', 'boolean'], 'notes' => ['nullable', 'string'], 'metadata' => ['nullable', 'array']]);
        $match = $create->execute($values);

        return response()->json(['data' => $this->resource($match)], 201);
    }

    public function analyze(Request $request, AnalyzeDnaMatch $analyzer): JsonResponse
    {
        $values = $request->validate([
            'kit_a' => ['required', 'array'],
            'kit_b' => ['required', 'array'],
        ]);

        return response()->json(['data' => $analyzer->analyze($values['kit_a'], $values['kit_b'])]);
    }

    public function compare(Request $request, CompareDnaKits $compare): JsonResponse
    {
        $values = $request->validate([
            'kit_a' => ['required', 'uuid', 'different:kit_b', Rule::exists('genealogy_dna_kits', 'id')->where('team_id', app(TeamContext::class)->require())],
            'kit_b' => ['required', 'uuid', Rule::exists('genealogy_dna_kits', 'id')->where('team_id', app(TeamContext::class)->require())],
        ]);
        $kitA = DnaKit::query()->findOrFail($values['kit_a']);
        $kitB = DnaKit::query()->findOrFail($values['kit_b']);

        return response()->json(['data' => $compare->execute($kitA, $kitB)]);
    }

    public function compareAndPersist(Request $request, PersistDnaComparison $compare): JsonResponse
    {
        $values = $request->validate([
            'kit_a' => ['required', 'uuid', 'different:kit_b', Rule::exists('genealogy_dna_kits', 'id')->where('team_id', app(TeamContext::class)->require())],
            'kit_b' => ['required', 'uuid', Rule::exists('genealogy_dna_kits', 'id')->where('team_id', app(TeamContext::class)->require())],
        ]);
        $kitA = DnaKit::query()->findOrFail($values['kit_a']);
        $kitB = DnaKit::query()->findOrFail($values['kit_b']);

        return response()->json(['data' => $compare->execute($kitA, $kitB)], 201);
    }

    public function triangulate(Request $request, TriangulateDna $triangulator): JsonResponse
    {
        $values = $request->validate([
            'matches' => ['required', 'array', 'min:3'],
            'minimum_shared_cm' => ['sometimes', 'numeric', 'min:0'],
        ]);

        return response()->json(['data' => $triangulator->execute($values['matches'], (float) ($values['minimum_shared_cm'] ?? 20.0))]);
    }

    public function show(DnaMatch $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record->load('segments'))]);
    }

    public function update(Request $request, DnaMatch $record, UpdateDnaMatch $update): JsonResponse
    {
        $values = $request->validate(['display_name' => ['sometimes', 'nullable', 'string', 'max:255'], 'predicted_relationship' => ['sometimes', 'nullable', 'string', 'max:100'], 'confidence' => ['sometimes', 'nullable', 'integer', 'between:0,100'], 'status' => ['sometimes', 'string', 'max:50'], 'is_private' => ['sometimes', 'boolean'], 'notes' => ['sometimes', 'nullable', 'string'], 'metadata' => ['sometimes', 'nullable', 'array']]);

        return response()->json(['data' => $this->resource($update->execute($record, $values))]);
    }

    public function destroy(DnaMatch $record, DeleteDnaMatch $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    public function segment(Request $request, DnaMatch $record, CreateDnaSegment $create): JsonResponse
    {
        $values = $request->validate(['chromosome' => ['required', 'integer', 'between:1,99'], 'start_position' => ['required', 'integer', 'min:0'], 'end_position' => ['required', 'integer', 'gt:start_position'], 'centimorgans' => ['nullable', 'numeric', 'min:0'], 'snps' => ['nullable', 'integer', 'min:0'], 'side' => ['nullable', 'string', 'max:50'], 'metadata' => ['nullable', 'array'], 'match_id' => ['prohibited']]);
        $segment = $create->execute([...$values, 'match_id' => $record->getKey()]);

        return response()->json(['data' => $this->segmentResource($segment)], 201);
    }

    public function segments(Request $request, DnaMatch $record): JsonResponse
    {
        $values = $request->validate(['page' => ['sometimes', 'array'], 'page.size' => ['sometimes', 'integer', 'between:1,100']]);
        $segments = $record->segments()->latest()->paginate($values['page']['size'] ?? 25);

        return response()->json([
            'data' => $segments->getCollection()->map(fn (DnaSegment $segment): array => $this->segmentResource($segment))->all(),
            'meta' => ['current_page' => $segments->currentPage(), 'per_page' => $segments->perPage(), 'total' => $segments->total()],
        ]);
    }

    public function updateSegment(Request $request, DnaSegment $record, UpdateDnaSegment $update): JsonResponse
    {
        $values = $request->validate([
            'chromosome' => ['sometimes', 'integer', 'between:1,99'],
            'start_position' => ['sometimes', 'integer', 'min:0'],
            'end_position' => ['sometimes', 'integer', 'gt:start_position'],
            'centimorgans' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'snps' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'side' => ['sometimes', 'nullable', 'string', 'max:50'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        return response()->json(['data' => $this->segmentResource($update->execute($record, $values))]);
    }

    public function deleteSegment(DnaSegment $record, DeleteDnaSegment $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    /** @return array<string, mixed> */
    private function resource(DnaMatch $match): array
    {
        return ['id' => $match->getKey(), 'type' => 'genealogy-dna-match', 'attributes' => ['kit_id' => $match->kit_id, 'external_id' => $match->external_id, 'display_name' => $match->display_name, 'predicted_relationship' => $match->predicted_relationship, 'confidence' => $match->confidence, 'total_cm' => $match->total_cm, 'shared_segments' => $match->shared_segments, 'status' => $match->status, 'is_private' => $match->is_private, 'notes' => $match->notes, 'metadata' => $match->metadata], 'relationships' => ['segments' => $match->relationLoaded('segments') ? $match->segments->map(fn (DnaSegment $segment): array => $this->segmentResource($segment))->all() : []]];
    }

    /** @return array<string, mixed> */
    private function segmentResource(DnaSegment $segment): array
    {
        return ['id' => $segment->getKey(), 'type' => 'genealogy-dna-segment', 'attributes' => [
            'match_id' => $segment->match_id,
            'chromosome' => $segment->chromosome,
            'start_position' => $segment->start_position,
            'end_position' => $segment->end_position,
            'centimorgans' => $segment->centimorgans,
            'snps' => $segment->snps,
            'side' => $segment->side,
            'metadata' => $segment->metadata,
        ]];
    }
}
