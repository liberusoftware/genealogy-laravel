<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Livewire;

use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\Dna\Models\DnaNote;
use Liberu\Genealogy\Dna\Models\DnaRelationship;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class DnaAnnotationList extends Component
{
    public ?string $matchId = null;

    public function render(): mixed
    {
        abort_unless(auth()->check(), 403);
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        if ($teamId === null) {
            return view('genealogy-dna-livewire::annotations', ['notes' => collect(), 'relationships' => collect()]);
        }

        [$notes, $relationships] = app(TeamContext::class)->run($teamId, function (): array {
            return [
                DnaNote::query()
                    ->when($this->matchId !== null, fn ($query) => $query
                        ->where('noteable_type', DnaMatch::class)
                        ->where('noteable_id', $this->matchId))
                    ->latest()
                    ->limit(25)
                    ->get(),
                DnaRelationship::query()->when($this->matchId !== null, fn ($query) => $query->where('match_id', $this->matchId))->latest()->limit(25)->get(),
            ];
        });

        return view('genealogy-dna-livewire::annotations', compact('notes', 'relationships'));
    }
}
