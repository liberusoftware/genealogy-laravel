<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Livewire;

use Illuminate\Validation\Rule;
use Liberu\Genealogy\Reports\Actions\GenerateGenealogyReport;
use Liberu\Genealogy\Reports\Models\GenealogyReport;
use Livewire\Component;

final class GenealogyReportList extends Component
{
    public string $status = '';

    public string $format = 'json';

    public string $rootPersonId = '';

    public ?string $generatedReportId = null;

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(GenealogyReport::STATUSES)],
            'format' => ['required', Rule::in(['json', 'csv', 'gedcom', 'svg'])],
            'rootPersonId' => ['nullable', 'uuid'],
        ];
    }

    public function updatedStatus(): void
    {
        $this->validateOnly('status');
    }

    public function generate(string $id, GenerateGenealogyReport $generate): void
    {
        $this->validate([
            'format' => $this->rules()['format'],
            'rootPersonId' => $this->rules()['rootPersonId'],
        ]);

        $generate->execute(GenealogyReport::query()->findOrFail($id), array_filter([
            'format' => $this->format,
            'root_person_id' => $this->rootPersonId !== '' ? $this->rootPersonId : null,
        ]));
        $this->generatedReportId = $id;
        $this->dispatch('genealogy-report-generated', id: $id);
    }

    public function render(): mixed
    {
        return view('genealogy-reports-livewire::list', [
            'records' => GenealogyReport::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
