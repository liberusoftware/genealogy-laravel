<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\ImportExport\Actions\DeleteDataTransfer;
use Liberu\Genealogy\ImportExport\Actions\UpdateDataTransfer;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource;

final class EditDataTransfer extends EditRecord
{
    protected static string $resource = DataTransferResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateDataTransfer::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteDataTransfer::class)->execute($record))];
    }
}
