<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource;

final class EditDiscoveryMatch extends EditRecord
{
    protected static string $resource = DiscoveryMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
