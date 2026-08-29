<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Research\Actions\DeleteResearchEntry;
use Liberu\Genealogy\Research\Actions\UpdateResearchEntry;
use Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource;

final class EditResearchEntry extends EditRecord
{
    protected static string $resource = ResearchEntryResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateResearchEntry::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteResearchEntry::class)->execute($record))];
    }
}
