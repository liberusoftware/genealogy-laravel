<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Livewire;

use Liberu\Genealogy\Collaboration\Actions\AcceptCollaborationInvitation;
use Liberu\Genealogy\Collaboration\Actions\InviteCollaborationMember;
use Liberu\Genealogy\Collaboration\Actions\RevokeCollaborationInvitation;
use Liberu\Genealogy\Collaboration\Models\CollaborationInvitation;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class CollaborationInvitationList extends Component
{
    public string $status = '';

    public string $email = '';

    public string $role = 'contributor';

    public ?string $spaceId = null;

    public function invite(InviteCollaborationMember $invite): void
    {
        abort_unless(auth()->check(), 403);
        $values = $this->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:'.implode(',', CollaborationInvitation::ROLES)],
            'spaceId' => ['nullable', 'uuid'],
        ]);
        $invite->execute([
            'email' => $values['email'],
            'role' => $values['role'],
            'space_id' => $values['spaceId'],
            'invited_by' => auth()->id(),
        ]);
        $this->reset('email', 'spaceId');
        $this->dispatch('collaboration-invitation-created');
    }

    public function accept(string $id, AcceptCollaborationInvitation $accept): void
    {
        abort_unless(auth()->check(), 403);
        $accept->execute($this->invitation($id), auth()->user());
        $this->dispatch('collaboration-invitation-accepted');
    }

    public function revoke(string $id, RevokeCollaborationInvitation $revoke): void
    {
        abort_unless(auth()->check(), 403);
        $revoke->execute($this->invitation($id));
        $this->dispatch('collaboration-invitation-revoked');
    }

    public function render(): mixed
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $records = $teamId === null ? collect() : app(TeamContext::class)->run($teamId, fn () => CollaborationInvitation::query()->when($this->status !== '', fn ($query) => $query->where('status', $this->status))->latest()->limit(25)->get());

        return view('genealogy-collaboration-livewire::invitations', ['records' => $records]);
    }

    private function invitation(string $id): CollaborationInvitation
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        abort_unless($teamId !== null, 403);

        return app(TeamContext::class)->run($teamId, fn (): CollaborationInvitation => CollaborationInvitation::query()->findOrFail($id));
    }
}
