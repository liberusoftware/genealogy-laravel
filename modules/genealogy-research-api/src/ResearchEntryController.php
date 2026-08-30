<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\Research\Actions\CreateResearchEntry;
use Liberu\Genealogy\Research\Actions\DeleteResearchEntry;
use Liberu\Genealogy\Research\Actions\UpdateResearchEntry;
use Liberu\Genealogy\Research\Models\ResearchEntry;
use Liberu\Genealogy\Research\Models\ResearchProject;

final class ResearchEntryController
{
    public function index(Request $request, ResearchProject $project): JsonResponse
    {
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
            'kind' => ['sometimes', 'in:'.implode(',', ResearchEntry::KINDS)],
            'status' => ['sometimes', 'in:'.implode(',', ResearchEntry::STATUSES)],
            'overdue' => ['sometimes', 'boolean'],
        ]);
        $entries = $project->entries()
            ->when(isset($values['kind']), fn ($query) => $query->where('kind', $values['kind']))
            ->when(isset($values['status']), fn ($query) => $query->where('status', $values['status']))
            ->when(($values['overdue'] ?? false), fn ($query) => $query->whereNotNull('due_date')->whereDate('due_date', '<', today())->where('status', '<>', 'completed'))
            ->latest()
            ->paginate($values['page']['size'] ?? 25);

        return response()->json(['data' => $entries->getCollection()->map(fn (ResearchEntry $entry): array => $this->resource($entry))->values()->all(), 'meta' => ['current_page' => $entries->currentPage(), 'per_page' => $entries->perPage(), 'total' => $entries->total()]]);
    }

    public function store(Request $request, ResearchProject $project, CreateResearchEntry $create): JsonResponse
    {
        $entry = $create->execute($this->validated($request) + ['research_project_id' => $project->getKey()]);

        return response()->json(['data' => $this->resource($entry)], 201);
    }

    public function show(ResearchProject $project, ResearchEntry $entry): JsonResponse
    {
        abort_unless((string) $entry->research_project_id === (string) $project->getKey(), 404);

        return response()->json(['data' => $this->resource($entry)]);
    }

    public function update(Request $request, ResearchProject $project, ResearchEntry $entry, UpdateResearchEntry $update): JsonResponse
    {
        abort_unless((string) $entry->research_project_id === (string) $project->getKey(), 404);
        $entry = $update->execute($entry, $this->validated($request, required: false));

        return response()->json(['data' => $this->resource($entry)]);
    }

    public function destroy(ResearchProject $project, ResearchEntry $entry, DeleteResearchEntry $delete): JsonResponse
    {
        abort_unless((string) $entry->research_project_id === (string) $project->getKey(), 404);
        $delete->execute($entry);

        return response()->json(status: 204);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $required = true): array
    {
        return $request->validate([
            'kind' => [$required ? 'required' : 'sometimes', Rule::in(ResearchEntry::KINDS)],
            'title' => [$required ? 'required' : 'sometimes', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:50000'],
            'status' => ['sometimes', 'in:'.implode(',', ResearchEntry::STATUSES)],
            'due_date' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ]);
    }

    /** @return array<string, mixed> */
    private function resource(ResearchEntry $entry): array
    {
        return ['id' => $entry->getKey(), 'type' => 'genealogy-research-entry', 'attributes' => ['research_project_id' => $entry->research_project_id, 'kind' => $entry->kind, 'title' => $entry->title, 'body' => $entry->body, 'status' => $entry->status, 'due_date' => $entry->due_date?->toDateString(), 'completed_at' => $entry->completed_at?->toISOString(), 'metadata' => $entry->metadata]];
    }
}
