<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\TreeViewer\Models\TreeView;

final class CreateTreeView
{
    public function execute(array $attributes): TreeView
    {
        $attributes = Arr::only($attributes, ['name', 'status', 'root_person_id', 'is_public', 'metadata']);
        $this->guardVisibility($attributes);

        return TreeView::query()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    private function guardVisibility(array $attributes): void
    {
        if (! ($attributes['is_public'] ?? false) || ! isset($attributes['root_person_id'])) {
            return;
        }

        $person = Person::query()->find($attributes['root_person_id']);

        if (! $person) {
            throw new InvalidArgumentException('The tree root person must belong to the active team.');
        }

        if ($person->isLiving()) {
            throw new InvalidArgumentException('A public tree cannot expose a living root person.');
        }
    }
}
