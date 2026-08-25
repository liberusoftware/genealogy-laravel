<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\CreateDnaKit as CreateDnaKitAction;
use Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource;

final class CreateDnaKit extends CreateRecord
{
    protected static string $resource = DnaKitResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateDnaKitAction::class)->execute($data);
    }
}
