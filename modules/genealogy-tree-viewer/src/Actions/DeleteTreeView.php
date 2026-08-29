<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\TreeViewer\Events\TreeViewDeleted;
use Liberu\Genealogy\TreeViewer\Models\TreeView;

final class DeleteTreeView
{
    public function execute(TreeView $tree): void
    {
        if ((string) $tree->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The tree must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $tree->delete());
        event(new TreeViewDeleted($tree));
    }
}
