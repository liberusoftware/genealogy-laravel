<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\Events\TreeUpdated;
use Liberu\Genealogy\GenealogyCore\Models\Tree;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class SetTreeOwner
{
    public function execute(Tree $tree, string|int|null $userId): Tree
    {
        $teamId = app(TeamContext::class)->require();
        if ((string) $tree->team_id !== $teamId) {
            throw new InvalidArgumentException('The tree must belong to the active team.');
        }

        if ($userId !== null && ! $this->userBelongsToTeam($userId, $teamId)) {
            throw new InvalidArgumentException('The tree owner must be an active member of the team.');
        }

        DB::transaction(fn (): bool => $tree->update(['user_id' => $userId]));
        $updated = $tree->refresh();
        event(new TreeUpdated($updated));

        return $updated;
    }

    private function userBelongsToTeam(string|int $userId, string $teamId): bool
    {
        return DB::table('teams')->where('id', $teamId)->where('user_id', $userId)->exists()
            || DB::table('team_user')->where('team_id', $teamId)->where('user_id', $userId)->where('status', 'active')->exists();
    }
}
