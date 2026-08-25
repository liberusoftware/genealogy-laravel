<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Livewire;

use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationProposal;
use Liberu\Genealogy\Collaboration\Actions\ReviewCollaborationProposal;
use Liberu\Genealogy\Collaboration\Actions\UpdateCollaborationProposal;
use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class CollaborationProposalEditor extends Component
{
    public ?string $proposalId = null;

    public string $title = '';

    public string $description = '';

    public function mount(?string $proposalId = null): void
    {
        $this->proposalId = $proposalId;
        if ($proposalId === null) {
            return;
        }

        $proposal = $this->proposal();
        $this->title = $proposal->title;
        $this->description = (string) ($proposal->description ?? '');
    }

    public function save(CreateCollaborationProposal $create, UpdateCollaborationProposal $update): void
    {
        $values = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
        ]);
        abort_unless(auth()->check(), 403);

        if ($this->proposalId === null) {
            $proposal = $create->execute($values + ['proposer_id' => auth()->id()]);
            $this->proposalId = (string) $proposal->getKey();
            $this->dispatch('collaboration-proposal-created');

            return;
        }

        $update->execute($this->proposal(), $values);
        $this->dispatch('collaboration-proposal-updated');
    }

    public function review(string $status, ReviewCollaborationProposal $review): void
    {
        $this->validate(['proposalId' => ['required', 'uuid']]);
        abort_unless(in_array($status, ['in_review', 'approved', 'rejected'], true), 422);
        $review->execute($this->proposal(), $status, auth()->id());
        $this->dispatch('collaboration-proposal-reviewed');
    }

    public function render(): mixed
    {
        return view('genealogy-collaboration-livewire::editor', ['proposal' => $this->proposalId === null ? null : $this->proposal()]);
    }

    private function proposal(): CollaborationProposal
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        abort_unless($teamId !== null, 403);

        return app(TeamContext::class)->run($teamId, fn (): CollaborationProposal => CollaborationProposal::query()->findOrFail($this->proposalId));
    }
}
