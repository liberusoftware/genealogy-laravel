<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Research\Actions\DeleteResearchProject;
use Liberu\Genealogy\Research\Actions\UpdateResearchProject;
use Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource;

final class EditResearchProject extends EditRecord
{
    protected static string $resource = ResearchProjectResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateResearchProject::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteResearchProject::class)->execute($record))];
    }
}
