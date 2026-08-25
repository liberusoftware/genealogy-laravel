<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Api\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Liberu\Genealogy\GenealogyCore\Actions\CreateTree;
use Liberu\Genealogy\GenealogyCore\Actions\DeleteTree;
use Liberu\Genealogy\GenealogyCore\Actions\SetTreeOwner;
use Liberu\Genealogy\GenealogyCore\Actions\SetTreeVisibility;
use Liberu\Genealogy\GenealogyCore\Actions\UpdateTree;
use Liberu\Genealogy\GenealogyCore\Api\Http\Resources\TreeResource;
use Liberu\Genealogy\GenealogyCore\Models\Tree;
use Liberu\Genealogy\GenealogyCore\Policies\TreePolicy;

final class TreeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
            'status' => ['sometimes', 'string', 'in:draft,active,archived'],
            'search' => ['sometimes', 'string', 'max:100'],
        ]);
        $actor = $request->user();
        $query = Tree::query()->latest('created_at');

        if ($actor === null) {
            $query->public();
        } else {
            $query->where(function ($trees) use ($actor): void {
                $trees->public()->orWhere('user_id', $actor->getAuthIdentifier());
            });
        }

        if (isset($values['status'])) {
            $query->where('status', $values['status']);
        }

        if (isset($values['search'])) {
            $query->where('name', 'like', '%'.addcslashes($values['search'], '%_').'%');
        }

        $trees = $query->paginate($values['page']['size'] ?? 25);

        return response()->json([
            'data' => $trees->getCollection()
                ->map(fn (Tree $tree): array => (new TreeResource($tree))->toArray($request))
                ->values()
                ->all(),
            'meta' => [
                'current_page' => $trees->currentPage(),
                'per_page' => $trees->perPage(),
                'total' => $trees->total(),
            ],
        ]);
    }

    public function show(Request $request, Tree $tree): TreeResource
    {
        abort_unless((new TreePolicy())->view($request->user(), $tree), 404);

        return new TreeResource($tree);
    }

    public function store(Request $request, CreateTree $createTree): TreeResource
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:draft,active,archived'],
            'description' => ['nullable', 'string'],
            'root_person_id' => ['nullable', 'uuid'],
            'is_public' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
            'identifier' => ['nullable', 'string', 'alpha_dash', 'max:100'],
            'terminology' => ['nullable', 'array'],
            'terminology.*' => ['string', 'max:100'],
        ]);
        $attributes['user_id'] = $request->user()->getAuthIdentifier();

        return new TreeResource($createTree->execute($attributes));
    }

    public function update(Request $request, Tree $tree, UpdateTree $updateTree): TreeResource
    {
        abort_unless((new TreePolicy())->manage($request->user(), $tree), 403);
        $attributes = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:draft,active,archived'],
            'description' => ['nullable', 'string'],
            'root_person_id' => ['nullable', 'uuid'],
            'is_public' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
            'identifier' => ['sometimes', 'nullable', 'string', 'alpha_dash', 'max:100'],
            'terminology' => ['sometimes', 'nullable', 'array'],
            'terminology.*' => ['string', 'max:100'],
        ]);

        return new TreeResource($updateTree->execute($tree, $attributes));
    }

    public function visibility(Request $request, Tree $tree, SetTreeVisibility $visibility): TreeResource
    {
        abort_unless((new TreePolicy())->manage($request->user(), $tree), 403);
        $values = $request->validate(['is_public' => ['required', 'boolean']]);

        return new TreeResource($visibility->execute($tree, $values['is_public']));
    }

    public function owner(Request $request, Tree $tree, SetTreeOwner $owner): TreeResource
    {
        abort_unless((new TreePolicy())->manage($request->user(), $tree), 403);
        $values = $request->validate(['user_id' => ['nullable', 'integer', 'exists:users,id']]);

        return new TreeResource($owner->execute($tree, $values['user_id'] ?? null));
    }

    public function destroy(Request $request, Tree $tree, DeleteTree $delete): void
    {
        abort_unless((new TreePolicy())->manage($request->user(), $tree), 403);
        $delete->execute($tree);
    }
}
