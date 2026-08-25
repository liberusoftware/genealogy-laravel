<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Policies;

use Liberu\Genealogy\GenealogyCore\TeamContext;

/** Default fail-closed policy for tenant-owned genealogy aggregates. */
final class TeamOwnedPolicy
{
    public function viewAny(?object $actor): bool
    {
        return $actor !== null && app(TeamContext::class)->current() !== null;
    }

    public function view(?object $actor, object $record): bool
    {
        if ((bool) ($record->is_public ?? false)) {
            return true;
        }

        return $this->manages($actor, $record);
    }

    public function create(?object $actor): bool
    {
        return $this->viewAny($actor);
    }

    public function update(?object $actor, object $record): bool
    {
        return $this->manages($actor, $record);
    }

    public function delete(?object $actor, object $record): bool
    {
        return $this->manages($actor, $record);
    }

    private function manages(?object $actor, object $record): bool
    {
        if ($actor === null || ! isset($record->team_id)) {
            return false;
        }

        $team = app(TeamContext::class)->current();

        return $team !== null && (string) $record->team_id === (string) $team;
    }
}
