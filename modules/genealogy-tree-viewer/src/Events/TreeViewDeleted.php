<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Events;

use Liberu\Genealogy\TreeViewer\Models\TreeView;

final class TreeViewDeleted
{
    public bool $afterCommit = true;

    public function __construct(public TreeView $tree) {}
}
