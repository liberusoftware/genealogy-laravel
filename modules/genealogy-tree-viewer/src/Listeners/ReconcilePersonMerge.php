<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Listeners;

use Liberu\Genealogy\GenealogyCore\Models\Tree;
use Liberu\Genealogy\People\Events\PersonMerged;

final class ReconcilePersonMerge
{
    public function handle(PersonMerged $event): void
    {
        Tree::query()
            ->where('team_id', $event->primary->team_id)
            ->where('root_person_id', $event->duplicateId)
            ->update(['root_person_id' => $event->primary->getKey()]);
    }
}
