<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\TreeViewer\Models\TreeView;

final class UpdateTreeView
{
    /** @param array<string, mixed> $attributes */
    public function execute(TreeView $tree, array $attributes): TreeView
    {
        $values = Arr::only($attributes, ['name', 'status', 'root_person_id', 'is_public', 'metadata']);
        $rootId = array_key_exists('root_person_id', $values) ? $values['root_person_id'] : $tree->root_person_id;
        $isPublic = array_key_exists('is_public', $values) ? (bool) $values['is_public'] : (bool) $tree->is_public;

        if ($isPublic && $rootId !== null) {
            $person = Person::query()->find($rootId);

            if (! $person) {
                throw new InvalidArgumentException('The tree root person must belong to the active team.');
            }

            if ($person->isLiving()) {
                throw new InvalidArgumentException('A public tree cannot expose a living root person.');
            }
        }

        $tree->update($values);

        return $tree->refresh();
    }
}
