<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Livewire;

use Liberu\Genealogy\Dna\Actions\PersistDnaComparison;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class DnaKitComparison extends Component
{
    public ?string $kitA = null;

    public ?string $kitB = null;

    /** @var array<string, mixed>|null */
    public ?array $result = null;

    public function compare(PersistDnaComparison $compare): void
    {
        abort_unless(auth()->check(), 403);
        $values = $this->validate([
            'kitA' => ['required', 'uuid', 'different:kitB'],
            'kitB' => ['required', 'uuid'],
        ]);
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        abort_unless($teamId !== null, 403);
        $this->result = app(TeamContext::class)->run($teamId, function () use ($compare, $values): array {
            return $compare->execute(
                DnaKit::query()->findOrFail($values['kitA']),
                DnaKit::query()->findOrFail($values['kitB']),
            );
        });
        $this->dispatch('genealogy-dna-kits-compared');
    }

    public function render(): mixed
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $kits = $teamId === null ? collect() : app(TeamContext::class)->run($teamId, fn () => DnaKit::query()->whereNotNull('file_path')->where('consent_status', 'granted')->latest()->limit(50)->get());

        return view('genealogy-dna-livewire::comparison', ['kits' => $kits]);
    }
}
