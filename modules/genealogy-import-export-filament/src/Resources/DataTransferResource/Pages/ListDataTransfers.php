<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource;

final class ListDataTransfers extends ListRecords
{
    protected static string $resource = DataTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
