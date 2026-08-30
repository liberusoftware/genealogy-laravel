<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Livewire;

use Illuminate\Validation\Rule;
use Liberu\Genealogy\Dna\Actions\ImportDnaKit;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class DnaKitList extends Component
{
    public string $status = '';

    public string $name = '';

    public string $content = '';

    public string $consentStatus = 'pending';

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return ['status' => ['nullable', Rule::in(DnaKit::STATUSES)]];
    }

    public function updatedStatus(): void
    {
        $this->validateOnly('status');
    }

    public function import(ImportDnaKit $import): void
    {
        abort_unless(auth()->check(), 403);
        $values = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:104857600'],
            'consentStatus' => ['required', 'in:pending,granted,revoked'],
        ]);
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        abort_unless($teamId !== null, 403);
        app(TeamContext::class)->run($teamId, fn () => $import->execute($values['content'], [
            'name' => $values['name'],
            'consent_status' => $values['consentStatus'],
        ]));
        $this->reset('name', 'content');
        $this->consentStatus = 'pending';
        $this->dispatch('genealogy-dna-kit-imported');
    }

    public function render(): mixed
    {
        abort_unless(auth()->check(), 403);
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $records = $teamId === null ? collect() : app(TeamContext::class)->run($teamId, fn () => DnaKit::query()
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest()->limit(25)->get());

        return view('genealogy-dna-livewire::list', ['records' => $records]);
    }
}
