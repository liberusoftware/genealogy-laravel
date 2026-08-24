<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\Research\Actions\CreateResearchEntry;
use Liberu\Genealogy\Research\Models\ResearchEntry;
use Liberu\Genealogy\Research\Models\ResearchProject;

final class ResearchEntryController
{
    public function index(ResearchProject $project): JsonResponse
    {
        return response()->json(['data' => $project->entries()->latest()->paginate()]);
    }

    public function store(Request $request, ResearchProject $project, CreateResearchEntry $create): JsonResponse
    {
        $entry = $create->execute($this->validated($request) + ['research_project_id' => $project->getKey()]);

        return response()->json(['data' => $entry], 201);
    }

    public function show(ResearchProject $project, ResearchEntry $entry): JsonResponse
    {
        abort_unless((string) $entry->research_project_id === (string) $project->getKey(), 404);

        return response()->json(['data' => $entry]);
    }

    public function update(Request $request, ResearchProject $project, ResearchEntry $entry): JsonResponse
    {
        abort_unless((string) $entry->research_project_id === (string) $project->getKey(), 404);
        $entry->update($this->validated($request, required: false));

        return response()->json(['data' => $entry->refresh()]);
    }

    public function destroy(ResearchProject $project, ResearchEntry $entry): JsonResponse
    {
        abort_unless((string) $entry->research_project_id === (string) $project->getKey(), 404);
        $entry->delete();

        return response()->json(status: 204);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $required = true): array
    {
        return $request->validate([
            'kind' => [$required ? 'required' : 'sometimes', Rule::in(ResearchEntry::KINDS)],
            'title' => [$required ? 'required' : 'sometimes', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:50000'],
            'status' => ['sometimes', 'string', 'max:50'],
            'due_date' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ]);
    }
}
