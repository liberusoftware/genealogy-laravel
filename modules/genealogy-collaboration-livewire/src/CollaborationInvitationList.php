<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Livewire;

use Liberu\Genealogy\Collaboration\Models\CollaborationInvitation;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class CollaborationInvitationList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $records = $teamId === null ? collect() : app(TeamContext::class)->run($teamId, fn () => CollaborationInvitation::query()->when($this->status !== '', fn ($query) => $query->where('status', $this->status))->latest()->limit(25)->get());

        return view('genealogy-collaboration-livewire::invitations', ['records' => $records]);
    }
}
