<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Filament\Resources\PersonResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\People\Actions\DeletePerson;
use Liberu\Genealogy\People\Actions\UpdatePerson;
use Liberu\Genealogy\People\Filament\Resources\PersonResource;

final class EditPerson extends EditRecord
{
    protected static string $resource = PersonResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdatePerson::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeletePerson::class)->execute($record))];
    }
}
