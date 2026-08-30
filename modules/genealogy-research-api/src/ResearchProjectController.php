<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Research\Actions\CreateResearchProject;
use Liberu\Genealogy\Research\Actions\DeleteResearchProject;
use Liberu\Genealogy\Research\Actions\UpdateResearchProject;
use Liberu\Genealogy\Research\Models\ResearchProject;

final class ResearchProjectController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
            'status' => ['sometimes', 'in:'.implode(',', ResearchProject::STATUSES)],
        ]);
        $projects = ResearchProject::query()
            ->when(isset($values['status']), fn ($query) => $query->where('status', $values['status']))
            ->latest()
            ->paginate($values['page']['size'] ?? 25);

        return response()->json(['data' => $projects->getCollection()->map(fn (ResearchProject $project): array => $this->resource($project))->values()->all(), 'meta' => ['current_page' => $projects->currentPage(), 'per_page' => $projects->perPage(), 'total' => $projects->total()]]);
    }

    public function store(Request $request, CreateResearchProject $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'in:'.implode(',', ResearchProject::STATUSES)],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function show(ResearchProject $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, ResearchProject $record, UpdateResearchProject $update): JsonResponse
    {
        $record = $update->execute($record, $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:'.implode(',', ResearchProject::STATUSES)],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)]);
    }

    public function destroy(ResearchProject $record, DeleteResearchProject $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    /** @return array<string, mixed> */
    private function resource(ResearchProject $project): array
    {
        return ['id' => $project->getKey(), 'type' => 'genealogy-research-project', 'attributes' => ['name' => $project->name, 'status' => $project->status, 'metadata' => $project->metadata, 'created_at' => $project->created_at?->toISOString(), 'updated_at' => $project->updated_at?->toISOString()]];
    }
}
