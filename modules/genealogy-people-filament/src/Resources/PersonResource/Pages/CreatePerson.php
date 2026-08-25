<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Filament\Resources\PersonResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\People\Actions\CreatePerson as CreatePersonAction;
use Liberu\Genealogy\People\Filament\Resources\PersonResource;

final class CreatePerson extends CreateRecord
{
    protected static string $resource = PersonResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreatePersonAction::class)->execute($data);
    }
}
