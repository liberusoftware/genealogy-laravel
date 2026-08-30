<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Livewire;

use Liberu\Genealogy\Collaboration\Actions\ToggleCollaborationWatch;
use Liberu\Genealogy\Collaboration\Models\CollaborationWatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class CollaborationWatchList extends Component
{
    public function unwatch(string $type, string $id, ToggleCollaborationWatch $toggle): void
    {
        abort_unless(auth()->check(), 403);
        $toggle->execute($type, $id, auth()->id());
    }

    public function render(): mixed
    {
        abort_unless(auth()->check(), 403);
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $records = $teamId === null ? collect() : app(TeamContext::class)->run($teamId, fn () => CollaborationWatch::query()->where('user_id', auth()->id())->latest()->limit(50)->get());

        return view('genealogy-collaboration-livewire::watches', ['records' => $records]);
    }
}
