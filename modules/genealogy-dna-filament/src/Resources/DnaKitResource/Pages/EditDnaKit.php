<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource;

final class EditDnaKit extends EditRecord
{
    protected static string $resource = DnaKitResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
