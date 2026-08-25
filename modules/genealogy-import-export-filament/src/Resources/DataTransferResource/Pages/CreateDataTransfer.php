<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\ImportExport\Actions\CreateDataTransfer as CreateDataTransferAction;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource;

final class CreateDataTransfer extends CreateRecord
{
    protected static string $resource = DataTransferResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateDataTransferAction::class)->execute($data);
    }
}
