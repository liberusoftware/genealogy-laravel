<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Discovery\Actions\ScanDuplicateCandidates;
use Liberu\Genealogy\Discovery\Filament\Resources\DiscoveryMatchResource;

final class ListDiscoveryMatchs extends ListRecords
{
    protected static string $resource = DiscoveryMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('scanDuplicates')
                ->label('Scan duplicates')
                ->form([TextInput::make('threshold')->numeric()->default(0.7)->minValue(0)->maxValue(1)->required(), TextInput::make('limit')->numeric()->default(100)->minValue(1)->maxValue(1000)->required()])
                ->action(fn (array $data): array => app(ScanDuplicateCandidates::class)->execute((float) $data['threshold'], (int) $data['limit'])),
        ];
    }
}
