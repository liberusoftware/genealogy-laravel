<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\CreateDnaMatch as CreateDnaMatchAction;
use Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource;

final class CreateDnaMatch extends CreateRecord
{
    protected static string $resource = DnaMatchResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateDnaMatchAction::class)->execute($data);
    }
}
