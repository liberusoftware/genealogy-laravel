<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\Contracts\PersonReferenceResolver;
use Liberu\Genealogy\GenealogyCore\Events\TreeCreated;
use Liberu\Genealogy\GenealogyCore\Models\Tree;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class CreateTree
{
    public function __construct(private readonly ?PersonReferenceResolver $personReference = null) {}

    public function execute(array $attributes): Tree
    {
        $values = Arr::only($attributes, [
            'name', 'status', 'description', 'root_person_id', 'is_public', 'metadata', 'user_id', 'identifier', 'terminology',
        ]);
        $values['name'] = trim((string) ($values['name'] ?? ''));
        $values['team_id'] = app(TeamContext::class)->require();
        if ($values['name'] === '') {
            throw new InvalidArgumentException('A tree name is required.');
        }
        if (isset($values['status']) && ! in_array($values['status'], ['draft', 'active', 'archived'], true)) {
            throw new InvalidArgumentException('The tree status is invalid.');
        }
        if (isset($values['identifier']) && trim((string) $values['identifier']) === '') {
            throw new InvalidArgumentException('A tree identifier cannot be empty.');
        }
        if (isset($values['root_person_id']) && ! $this->personBelongsToTeam($values['root_person_id'], $values['team_id'])) {
            throw new InvalidArgumentException('The tree root person must belong to the active team.');
        }

        $tree = DB::transaction(function () use ($values): Tree {
            $tree = Tree::query()->create($values);

            return $tree;
        });
        event(new TreeCreated($tree));

        return $tree;
    }

    private function personBelongsToTeam(string|int $personId, string $teamId): bool
    {
        if ($this->personReference === null) {
            return true;
        }

        return $this->personReference->existsForTeam($personId, $teamId);
    }
}
