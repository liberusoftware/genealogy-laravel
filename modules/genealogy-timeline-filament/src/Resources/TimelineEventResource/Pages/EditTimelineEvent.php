<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Timeline\Actions\DeleteTimelineEvent;
use Liberu\Genealogy\Timeline\Actions\UpdateTimelineEvent as UpdateTimelineEventAction;
use Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource;

final class EditTimelineEvent extends EditRecord
{
    protected static string $resource = TimelineEventResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateTimelineEventAction::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteTimelineEvent::class)->execute($record))];
    }
}
