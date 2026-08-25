<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\GenealogyCore\Policies\TreePolicy;
use Liberu\Genealogy\TreeViewer\Actions\CreateTreeView;
use Liberu\Genealogy\TreeViewer\Actions\DeleteTreeView;
use Liberu\Genealogy\TreeViewer\Actions\UpdateTreeView;
use Liberu\Genealogy\TreeViewer\Api\Resources\TreeViewResource;
use Liberu\Genealogy\TreeViewer\Models\TreeView;
use Liberu\Genealogy\TreeViewer\Queries\TreeGraph;

final class TreeViewApiController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'status' => ['nullable', 'string', 'in:draft,active,completed,archived'],
            'public_only' => ['sometimes', 'boolean'],
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
        ]);
        $query = TreeView::query()->latest('created_at');

        if ($request->user() === null || ($values['public_only'] ?? false)) {
            $query->public();
        } else {
            $query->where(fn ($trees) => $trees->public()->orWhere('user_id', $request->user()->getAuthIdentifier()));
        }

        $trees = $query->when(isset($values['status']), fn ($trees) => $trees->where('status', $values['status']))
            ->paginate($values['page']['size'] ?? 25);

        return response()->json(TreeViewResource::collection($trees)->response()->getData(true));
    }

    public function store(Request $request, CreateTreeView $create): JsonResponse
    {
        $values = $request->validate($this->rules());
        $values['user_id'] = $request->user()->getAuthIdentifier();

        return response()->json(new TreeViewResource($create->execute($values)), 201);
    }

    public function show(Request $request, string $record): TreeViewResource
    {
        $tree = $this->tree($request, $record);
        $this->authorizeView($request, $tree);

        return new TreeViewResource($tree);
    }

    public function graph(Request $request, string $record, TreeGraph $graph): JsonResponse
    {
        $tree = $this->tree($request, $record);
        $this->authorizeView($request, $tree);
        abort_unless($tree->rootPerson, 422, 'This tree has no root person.');

        $values = $request->validate([
            'generations' => ['sometimes', 'integer', 'between:0,12'],
            'view' => ['sometimes', 'string', 'in:pedigree,descendants,fan,chart'],
            'include_living' => ['sometimes', 'boolean'],
            'include_siblings' => ['sometimes', 'boolean'],
        ]);

        return response()->json(['data' => $graph->for(
            $tree->rootPerson,
            (int) ($values['generations'] ?? 3),
            ! $tree->is_public && (bool) ($values['include_living'] ?? true),
            $values['view'] ?? 'chart',
            (bool) ($values['include_siblings'] ?? false),
        )]);
    }

    public function update(Request $request, string $record, UpdateTreeView $update): TreeViewResource
    {
        $tree = $this->tree($request, $record);
        abort_unless((new TreePolicy())->manage($request->user(), $tree), 403);

        return new TreeViewResource($update->execute($tree, $request->validate($this->rules(false))));
    }

    public function destroy(Request $request, string $record, DeleteTreeView $delete): void
    {
        $tree = $this->tree($request, $record);
        abort_unless((new TreePolicy())->manage($request->user(), $tree), 403);
        $delete->execute($tree);
    }

    /** @return array<string, list<string>> */
    private function rules(bool $creating = true): array
    {
        return [
            'name' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:draft,active,completed,archived'],
            'root_person_id' => ['sometimes', 'nullable', 'uuid'],
            'is_public' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    private function tree(Request $request, string $id): TreeView
    {
        $query = TreeView::query();

        if ($request->user() === null) {
            $query->withoutGlobalScope('team');
        }

        return $query->findOrFail($id);
    }

    private function authorizeView(Request $request, TreeView $tree): void
    {
        abort_unless((new TreePolicy())->view($request->user(), $tree), 404);
    }
}
