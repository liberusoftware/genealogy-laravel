<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Timeline\Actions\CreateTimelineEvent as CreateTimelineEventAction;
use Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource;

final class CreateTimelineEvent extends CreateRecord
{
    protected static string $resource = TimelineEventResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateTimelineEventAction::class)->execute($data);
    }
}
