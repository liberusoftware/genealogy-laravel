<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\Events\TreeUpdated;
use Liberu\Genealogy\GenealogyCore\Models\Tree;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class SetTreeVisibility
{
    public function execute(Tree $tree, bool $isPublic): Tree
    {
        if ((string) $tree->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The tree must belong to the active team.');
        }

        DB::transaction(function () use ($tree, $isPublic): void {
            $tree->update(['is_public' => $isPublic]);
        });

        $tree = $tree->refresh();
        event(new TreeUpdated($tree));

        return $tree;
    }
}
