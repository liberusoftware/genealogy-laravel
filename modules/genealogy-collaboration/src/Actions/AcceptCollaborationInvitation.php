<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationInvitation;
use Liberu\Genealogy\Collaboration\Models\CollaborationMembership;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class AcceptCollaborationInvitation
{
    public function execute(CollaborationInvitation $invitation, Authenticatable $actor): CollaborationMembership
    {
        return app(TeamContext::class)->run($invitation->team_id, function () use ($invitation, $actor): CollaborationMembership {
            if ($invitation->status !== 'pending' || ($invitation->expires_at !== null && now()->greaterThan($invitation->expires_at))) {
                throw new InvalidArgumentException('The collaboration invitation is no longer valid.');
            }
            if (isset($actor->email) && mb_strtolower((string) $actor->email) !== $invitation->email) {
                throw new InvalidArgumentException('The authenticated user does not match the invitation.');
            }

            $membership = DB::transaction(function () use ($invitation, $actor): CollaborationMembership {
                $membership = CollaborationMembership::query()->updateOrCreate(
                    ['space_id' => $invitation->space_id, 'user_id' => $actor->getAuthIdentifier()],
                    ['team_id' => $invitation->team_id, 'role' => $invitation->role, 'status' => 'active', 'joined_at' => now()],
                );
                $invitation->update(['status' => 'accepted', 'accepted_at' => now()]);

                return $membership;
            });
            app(RecordCollaborationAttribution::class)->execute('invitation', (string) $invitation->getKey(), 'accepted', [], (string) $actor->getAuthIdentifier());

            return $membership;
        });
    }
}
