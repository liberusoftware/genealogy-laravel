<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\TreeViewer\Actions\CreateTreeView;
use Liberu\Genealogy\TreeViewer\Actions\DeleteTreeView;
use Liberu\Genealogy\TreeViewer\Actions\UpdateTreeView;
use Liberu\Genealogy\TreeViewer\Api\Resources\TreeViewResource;
use Liberu\Genealogy\TreeViewer\Models\TreeView;
use Liberu\Genealogy\TreeViewer\Queries\TreeGraph;

final class TreeViewController
{
    public function index(): JsonResponse
    {
        return response()->json(TreeViewResource::collection(TreeView::query()->latest()->paginate())->response()->getData(true));
    }

    public function store(Request $request, CreateTreeView $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'in:draft,active,completed,archived'],
            'root_person_id' => ['nullable', 'uuid'],
            'is_public' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(new TreeViewResource($record), 201);
    }

    public function show(TreeView $record): JsonResponse
    {
        return response()->json(new TreeViewResource($record));
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
        $updated = $update->execute($record, $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:draft,active,completed,archived'],
            'root_person_id' => ['sometimes', 'nullable', 'uuid'],
            'is_public' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(new TreeViewResource($updated));
    }

    public function destroy(TreeView $record, DeleteTreeView $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }
}
