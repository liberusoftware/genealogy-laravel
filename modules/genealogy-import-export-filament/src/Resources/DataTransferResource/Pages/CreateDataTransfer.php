<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource;

final class CreateDataTransfer extends CreateRecord
{
    protected static string $resource = DataTransferResource::class;
}
