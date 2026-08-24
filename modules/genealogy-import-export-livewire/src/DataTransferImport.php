<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Livewire;

use Liberu\Genealogy\ImportExport\Importers\GenealogyImportService;
use Livewire\Component;
use Livewire\WithFileUploads;

final class DataTransferImport extends Component
{
    use WithFileUploads;

    public mixed $file = null;

    /** @var array<string, mixed>|null */
    public ?array $report = null;

    public function preview(GenealogyImportService $service): void
    {
        $this->validate(['file' => ['required', 'file', 'max:10240']]);
        $this->report = $service->preview((string) $this->file->get());
    }

    public function import(GenealogyImportService $service): void
    {
        $this->validate(['file' => ['required', 'file', 'max:10240']]);
        $report = $service->import((string) $this->file->get(), false);
        $this->report = $report;
        $this->dispatch('genealogy-import-completed', report: $report);
    }

    public function render(): mixed
    {
        return view('genealogy-import-export-livewire::import');
    }
}
