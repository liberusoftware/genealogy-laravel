<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Livewire;

use Liberu\Genealogy\ImportExport\Actions\ExportGenealogyData;
use Livewire\Component;

final class DataTransferExport extends Component
{
    public string $name = 'Genealogy export';

    public string $format = 'gedcom';

    public function export(ExportGenealogyData $action): mixed
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'format' => ['required', 'in:gedcom,gramps-xml'],
        ]);

        $result = $action->execute($this->format, $this->name);
        $this->dispatch('genealogy-export-completed', transfer: (string) $result->transfer->getKey());

        return response()->streamDownload(static function () use ($result): void {
            echo $result->content;
        }, $result->filename, ['Content-Type' => $result->contentType]);
    }

    public function render(): mixed
    {
        return view('genealogy-import-export-livewire::export');
    }
}
