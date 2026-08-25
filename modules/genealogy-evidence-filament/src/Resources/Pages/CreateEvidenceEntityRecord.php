<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Evidence\Actions\CreateEvidenceEntity;

abstract class CreateEvidenceEntityRecord extends CreateRecord
{
    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateEvidenceEntity::class)->execute(static::getResource()::getModel(), $data);
    }
}
