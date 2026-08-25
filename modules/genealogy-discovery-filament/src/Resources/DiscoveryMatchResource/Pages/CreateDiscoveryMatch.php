<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource;

final class CreateDiscoveryMatch extends CreateRecord
{
    protected static string $resource = DiscoveryMatchResource::class;
}
