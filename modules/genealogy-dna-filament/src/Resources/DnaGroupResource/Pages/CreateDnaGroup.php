<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaGroupResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\CreateDnaGroup as CreateDnaGroupAction;
use Liberu\Genealogy\Dna\Filament\Resources\DnaGroupResource;

final class CreateDnaGroup extends CreateRecord
{
    protected static string $resource = DnaGroupResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateDnaGroupAction::class)->execute($data);
    }
}
