<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Evidence\Actions\DeleteEvidenceEntity;
use Liberu\Genealogy\Evidence\Actions\UpdateEvidenceEntity;

abstract class EditEvidenceEntityRecord extends EditRecord
{
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateEvidenceEntity::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteEvidenceEntity::class)->execute($record))];
    }
}
