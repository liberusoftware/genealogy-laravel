<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaProviderResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\CreateDnaProvider as CreateDnaProviderAction;
use Liberu\Genealogy\Dna\Filament\Resources\DnaProviderResource;

final class CreateDnaProvider extends CreateRecord
{
    protected static string $resource = DnaProviderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateDnaProviderAction::class)->execute($data);
    }
}
