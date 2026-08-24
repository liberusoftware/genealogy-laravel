<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource;

final class CreateTimelineEvent extends CreateRecord
{
    protected static string $resource = TimelineEventResource::class;
}
