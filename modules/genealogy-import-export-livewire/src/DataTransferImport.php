<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Livewire;

use Liberu\Genealogy\ImportExport\Actions\CreateDataTransfer;
use Liberu\Genealogy\ImportExport\Actions\UpdateDataTransfer;
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
        $this->validate(['file' => ['required', 'file', 'max:10240', 'mimes:ged,gedcom,xml,txt']]);
        $this->report = $service->preview((string) $this->file->get());
    }

    public function import(GenealogyImportService $service, CreateDataTransfer $create, UpdateDataTransfer $update): void
    {
        $this->validate(['file' => ['required', 'file', 'max:10240', 'mimes:ged,gedcom,xml,txt']]);
        $content = (string) $this->file->get();
        $preview = $service->preview($content);
        $transfer = $create->execute(['name' => 'Livewire genealogy import', 'format' => $preview['format'], 'direction' => 'import', 'status' => 'active', 'records_count' => $preview['people']]);
        try {
            $report = $service->import($content, false, $transfer);
        } catch (\Throwable $exception) {
            $update->execute($transfer, ['status' => 'failed', 'metadata' => ['error' => 'Import failed.']]);
            throw $exception;
        }
        $this->report = $report;
        $this->dispatch('genealogy-import-completed', report: $report);
    }

    public function render(): mixed
    {
        return view('genealogy-import-export-livewire::import');
    }
}
