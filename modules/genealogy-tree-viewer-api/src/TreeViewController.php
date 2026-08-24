<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\TreeViewer\Actions\CreateTreeView;
use Liberu\Genealogy\TreeViewer\Models\TreeView;

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
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(TreeView $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, TreeView $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(TreeView $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
