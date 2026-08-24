<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource;

final class EditDataTransfer extends EditRecord
{
    protected static string $resource = DataTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
