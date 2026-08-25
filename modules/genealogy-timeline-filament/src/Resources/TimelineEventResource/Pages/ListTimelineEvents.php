<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource;

final class ListTimelineEvents extends ListRecords
{
    protected static string $resource = TimelineEventResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
