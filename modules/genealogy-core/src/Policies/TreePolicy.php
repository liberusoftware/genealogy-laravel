<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Policies;

use Liberu\Genealogy\GenealogyCore\Models\Tree;

final class TreePolicy
{
    public function view(?object $actor, Tree $tree): bool
    {
        if ($tree->is_public) {
            return true;
        }

        if ($actor === null) {
            return false;
        }

        $actorId = $actor->getAuthIdentifier();

        return $actorId !== null && $tree->isOwnedBy($actorId);
    }

    public function manage(?object $actor, Tree $tree): bool
    {
        if ($actor === null) {
            return false;
        }

        $actorId = $actor->getAuthIdentifier();

        return $actorId !== null && $tree->isOwnedBy($actorId);
    }
}
