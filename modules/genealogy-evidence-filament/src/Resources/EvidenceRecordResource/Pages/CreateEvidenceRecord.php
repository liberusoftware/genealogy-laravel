<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Evidence\Actions\CreateEvidenceRecord as CreateEvidenceRecordAction;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource;

final class CreateEvidenceRecord extends CreateRecord
{
    protected static string $resource = EvidenceRecordResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateEvidenceRecordAction::class)->execute($data);
    }
}
