<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Discovery\Actions\DeleteDiscoveryMatch;
use Liberu\Genealogy\Discovery\Actions\UpdateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource;

final class EditDiscoveryMatch extends EditRecord
{
    protected static string $resource = DiscoveryMatchResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateDiscoveryMatch::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteDiscoveryMatch::class)->execute($record))];
    }
}
