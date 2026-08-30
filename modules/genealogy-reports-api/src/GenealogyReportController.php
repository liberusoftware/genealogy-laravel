<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Reports\Actions\CreateGenealogyReport;
use Liberu\Genealogy\Reports\Actions\DeleteGenealogyReport;
use Liberu\Genealogy\Reports\Actions\GenerateGenealogyReport;
use Liberu\Genealogy\Reports\Actions\UpdateGenealogyReport;
use Liberu\Genealogy\Reports\Models\GenealogyReport;

final class GenealogyReportController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
            'type' => ['sometimes', 'in:'.implode(',', GenealogyReport::TYPES)],
        ]);
        $reports = GenealogyReport::query()->when(isset($values['type']), fn ($query) => $query->where('type', $values['type']))->latest()->paginate($values['page']['size'] ?? 25);

        return response()->json(['data' => $reports->getCollection()->map(fn (GenealogyReport $report): array => $this->resource($report))->values()->all(), 'meta' => ['current_page' => $reports->currentPage(), 'per_page' => $reports->perPage(), 'total' => $reports->total()]]);
    }

    public function store(Request $request, CreateGenealogyReport $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['sometimes', 'in:'.implode(',', GenealogyReport::TYPES)],
            'status' => ['sometimes', 'in:'.implode(',', GenealogyReport::STATUSES)],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function show(GenealogyReport $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, GenealogyReport $record, UpdateGenealogyReport $update): JsonResponse
    {
        $record = $update->execute($record, $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:'.implode(',', GenealogyReport::TYPES)],
            'status' => ['sometimes', 'in:'.implode(',', GenealogyReport::STATUSES)],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)]);
    }

    public function destroy(GenealogyReport $record, DeleteGenealogyReport $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    public function generate(Request $request, GenealogyReport $record, GenerateGenealogyReport $generate): JsonResponse
    {
        $values = $request->validate(['format' => ['sometimes', 'in:json,csv,gedcom,svg'], 'parameters' => ['nullable', 'array']]);

        return response()->json(['data' => $this->resource($generate->execute($record, ($values['parameters'] ?? []) + ['format' => $values['format'] ?? 'json']))]);
    }

    /** @return array<string, mixed> */
    private function resource(GenealogyReport $report): array
    {
        return ['id' => $report->getKey(), 'type' => 'genealogy-report', 'attributes' => [
            'name' => $report->name, 'type' => $report->type, 'status' => $report->status,
            'metadata' => $report->metadata, 'created_at' => $report->created_at?->toISOString(), 'updated_at' => $report->updated_at?->toISOString(),
            'generated_output' => $report->generated_output, 'generated_at' => $report->generated_at?->toISOString(),
        ]];
    }
}
