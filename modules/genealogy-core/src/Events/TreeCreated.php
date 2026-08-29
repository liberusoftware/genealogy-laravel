<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Events;

use Liberu\Genealogy\GenealogyCore\Models\Tree;

final class TreeCreated
{
    public bool $afterCommit = true;

    public function __construct(public Tree $tree) {}
}
