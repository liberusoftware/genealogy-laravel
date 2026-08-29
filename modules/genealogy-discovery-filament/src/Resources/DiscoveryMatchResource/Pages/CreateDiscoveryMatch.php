<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Discovery\Actions\CreateDiscoveryMatch as CreateMatch;
use Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource;

final class CreateDiscoveryMatch extends CreateRecord
{
    protected static string $resource = DiscoveryMatchResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateMatch::class)->execute($data);
    }
}
