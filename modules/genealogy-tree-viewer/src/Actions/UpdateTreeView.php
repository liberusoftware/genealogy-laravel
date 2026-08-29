<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\Events\TreeUpdated;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\TreeViewer\Models\TreeView;

final class UpdateTreeView
{
    /** @param array<string, mixed> $attributes */
    public function execute(TreeView $tree, array $attributes): TreeView
    {
        if ((string) $tree->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The tree must belong to the active team.');
        }
        $values = Arr::only($attributes, ['name', 'status', 'root_person_id', 'is_public', 'metadata']);
        if (isset($values['status']) && ! in_array($values['status'], TreeView::STATUSES, true)) {
            throw new InvalidArgumentException('The tree view status is invalid.');
        }
        $rootId = array_key_exists('root_person_id', $values) ? $values['root_person_id'] : $tree->root_person_id;
        $isPublic = array_key_exists('is_public', $values) ? (bool) $values['is_public'] : (bool) $tree->is_public;

        if ($rootId !== null) {
            $person = Person::query()->find($rootId);

            if (! $person) {
                throw new InvalidArgumentException('The tree root person must belong to the active team.');
            }

            if ($isPublic && $person->isLiving()) {
                throw new InvalidArgumentException('A public tree cannot expose a living root person.');
            }
        }

        DB::transaction(fn (): bool => $tree->update($values));
        event(new TreeUpdated($tree->refresh()));

        return $tree;
    }
}
