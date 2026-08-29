<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\Contracts\PersonReferenceResolver;
use Liberu\Genealogy\GenealogyCore\Events\TreeUpdated;
use Liberu\Genealogy\GenealogyCore\Models\Tree;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateTree
{
    public function __construct(private readonly ?PersonReferenceResolver $personReference = null) {}

    public function execute(Tree $tree, array $attributes): Tree
    {
        if ((string) $tree->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The tree must belong to the active team.');
        }
        $values = Arr::only($attributes, [
            'name', 'status', 'description', 'root_person_id', 'is_public', 'metadata', 'identifier', 'terminology',
        ]);
        if (array_key_exists('identifier', $values) && $values['identifier'] !== null) {
            $values['identifier'] = trim((string) $values['identifier']);
        }
        $tree->fill($values);
        if ($tree->name !== null) {
            $tree->name = trim((string) $tree->name);
        }
        if ($tree->name === '') {
            throw new InvalidArgumentException('A tree name is required.');
        }
        if ($tree->root_person_id !== null && ! $this->personBelongsToTeam($tree->root_person_id, (string) $tree->team_id)) {
            throw new InvalidArgumentException('The tree root person must belong to the active team.');
        }
        if (! in_array($tree->status, ['draft', 'active', 'archived'], true)) {
            throw new InvalidArgumentException('The tree status is invalid.');
        }
        DB::transaction(fn (): bool => $tree->save());
        event(new TreeUpdated($tree));

        return $tree->refresh();
    }

    private function personBelongsToTeam(string|int $personId, string $teamId): bool
    {
        if ($this->personReference === null) {
            return true;
        }

        return $this->personReference->existsForTeam($personId, $teamId);
    }
}
