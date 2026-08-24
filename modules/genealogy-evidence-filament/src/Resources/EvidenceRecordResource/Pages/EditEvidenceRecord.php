<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource;

final class EditEvidenceRecord extends EditRecord
{
    protected static string $resource = EvidenceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
