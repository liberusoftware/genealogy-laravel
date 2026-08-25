<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\Events\TreeCreated;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\TreeViewer\Models\TreeView;

final class CreateTreeView
{
    public function execute(array $attributes): TreeView
    {
        $attributes = Arr::only($attributes, ['name', 'status', 'root_person_id', 'is_public', 'metadata']);
        $this->guardVisibility($attributes);
        $schema = TreeView::query()->getModel()->getConnection()->getSchemaBuilder();
        $attributes = Arr::only($attributes, $schema->getColumnListing('genealogy_trees'));
        $record = DB::transaction(function () use ($attributes, $schema): TreeView {
            $record = new TreeView();
            if (! $schema->hasColumn('genealogy_trees', 'is_public')) {
                $record->offsetUnset('is_public');
            }
            $record->fill($attributes);
            $record->save();

            return $record;
        });
        event(new TreeCreated($record));

        return $record;
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
