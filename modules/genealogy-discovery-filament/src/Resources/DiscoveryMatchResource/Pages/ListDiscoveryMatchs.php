<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource;

final class ListDiscoveryMatchs extends ListRecords
{
    protected static string $resource = DiscoveryMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
