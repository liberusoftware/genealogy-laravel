<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationMembership;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class SetCollaborationMembershipRole
{
    public function execute(CollaborationMembership $membership, string $role): CollaborationMembership
    {
        if ((string) $membership->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The membership must belong to the active team.');
        }
        if (! in_array($role, CollaborationMembership::ROLES, true)) {
            throw new InvalidArgumentException('The collaboration role is invalid.');
        }

        DB::transaction(fn (): bool => $membership->update(['role' => $role]));
        app(RecordCollaborationAttribution::class)->execute('membership', (string) $membership->getKey(), 'role_changed', ['role' => $role]);

        return $membership->refresh();
    }
}
