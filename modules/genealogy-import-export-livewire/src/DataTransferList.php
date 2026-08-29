<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Livewire;

use Illuminate\Validation\Rule;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;
use Livewire\Component;

final class DataTransferList extends Component
{
    public string $status = '';

    protected function rules(): array
    {
        return ['status' => ['nullable', Rule::in(DataTransfer::STATUSES)]];
    }

    public function updatedStatus(): void
    {
        $this->validateOnly('status');
    }

    public function render(): mixed
    {
        abort_unless(auth()->check(), 403);

        return view('genealogy-import-export-livewire::list', [
            'records' => DataTransfer::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
