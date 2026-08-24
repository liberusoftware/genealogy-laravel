<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource;

final class EditTimelineEvent extends EditRecord
{
    protected static string $resource = TimelineEventResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
