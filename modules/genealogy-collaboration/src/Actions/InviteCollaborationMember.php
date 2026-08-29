<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationInvitation;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class InviteCollaborationMember
{
    public function execute(array $attributes): CollaborationInvitation
    {
        $teamId = app(TeamContext::class)->require();
        $email = mb_strtolower(trim((string) ($attributes['email'] ?? '')));
        $role = (string) ($attributes['role'] ?? 'contributor');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid collaboration invitation email is required.');
        }
        if (! in_array($role, CollaborationInvitation::ROLES, true)) {
            throw new InvalidArgumentException('The collaboration role is invalid.');
        }

        $values = Arr::only($attributes, ['space_id', 'invited_by', 'expires_at']);
        $values += ['team_id' => $teamId, 'email' => $email, 'role' => $role, 'status' => 'pending'];

        $invitation = DB::transaction(fn (): CollaborationInvitation => CollaborationInvitation::query()->updateOrCreate(
            ['team_id' => $teamId, 'space_id' => $values['space_id'] ?? null, 'email' => $email],
            $values,
        ));
        app(RecordCollaborationAttribution::class)->execute('invitation', (string) $invitation->getKey(), 'invited', ['role' => $role], $values['invited_by'] ?? null);

        return $invitation;
    }
}
