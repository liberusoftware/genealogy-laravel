<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\TreeViewer\Actions\CreateTreeView;
use Liberu\Genealogy\TreeViewer\Actions\UpdateTreeView;
use Liberu\Genealogy\TreeViewer\Models\TreeView;
use Liberu\Genealogy\TreeViewer\Queries\TreeGraph;

final class TreeViewController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => TreeView::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateTreeView $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'root_person_id' => ['nullable', 'uuid'],
            'is_public' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(TreeView $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function graph(Request $request, TreeView $record, TreeGraph $graph): JsonResponse
    {
        abort_unless($record->rootPerson, 422, 'This tree has no root person.');

        if ($record->is_public && $record->rootPerson->isLiving()) {
            abort(403, 'A public tree cannot expose a living root person.');
        }

        return response()->json(['data' => $graph->for(
            $record->rootPerson,
            (int) $request->integer('generations', 3),
            ! $record->is_public,
        )]);
    }

    public function update(Request $request, TreeView $record, UpdateTreeView $update): JsonResponse
    {
        return response()->json(['data' => $update->execute($record, $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'root_person_id' => ['sometimes', 'nullable', 'uuid'],
            'is_public' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]))]);
    }

    public function destroy(TreeView $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
