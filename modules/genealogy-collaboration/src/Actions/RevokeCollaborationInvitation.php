<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationInvitation;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class RevokeCollaborationInvitation
{
    public function execute(CollaborationInvitation $invitation): CollaborationInvitation
    {
        if ((string) $invitation->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The invitation must belong to the active team.');
        }
        if ($invitation->status !== 'pending') {
            throw new InvalidArgumentException('Only pending invitations can be revoked.');
        }

        $invitation->update(['status' => 'revoked', 'revoked_at' => now()]);
        app(RecordCollaborationAttribution::class)->execute('invitation', (string) $invitation->getKey(), 'revoked');

        return $invitation->refresh();
    }
}
