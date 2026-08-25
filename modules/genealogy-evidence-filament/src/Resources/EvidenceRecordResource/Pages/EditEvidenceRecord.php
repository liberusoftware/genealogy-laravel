<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Evidence\Actions\DeleteEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\UpdateEvidenceRecord as UpdateEvidenceRecordAction;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource;

final class EditEvidenceRecord extends EditRecord
{
    protected static string $resource = EvidenceRecordResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateEvidenceRecordAction::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteEvidenceRecord::class)->execute($record))];
    }
}
