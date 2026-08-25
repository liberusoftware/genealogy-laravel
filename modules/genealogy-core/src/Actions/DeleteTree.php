<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\Events\TreeDeleted;
use Liberu\Genealogy\GenealogyCore\Models\Tree;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteTree
{
    public function execute(Tree $tree): void
    {
        if ((string) $tree->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The tree must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $tree->delete());
        event(new TreeDeleted($tree));
    }
}
