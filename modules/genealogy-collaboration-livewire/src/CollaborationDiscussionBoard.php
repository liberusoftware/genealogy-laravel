<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Livewire;

use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Models\CollaborationDiscussion;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class CollaborationDiscussionBoard extends Component
{
    public ?string $proposalId = null;

    public string $body = '';

    public function post(CreateCollaborationDiscussion $create): void
    {
        $values = $this->validate(['body' => ['required', 'string', 'max:50000']]);
        abort_unless(auth()->check(), 403);
        $create->execute($values + ['proposal_id' => $this->proposalId, 'author_id' => auth()->id()]);
        $this->reset('body');
        $this->dispatch('collaboration-discussion-created');
    }

    public function render(): mixed
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $records = $teamId === null ? collect() : app(TeamContext::class)->run($teamId, fn () => CollaborationDiscussion::query()->when($this->proposalId !== null, fn ($query) => $query->where('proposal_id', $this->proposalId))->latest()->limit(50)->get());

        return view('genealogy-collaboration-livewire::discussions', ['records' => $records]);
    }
}
