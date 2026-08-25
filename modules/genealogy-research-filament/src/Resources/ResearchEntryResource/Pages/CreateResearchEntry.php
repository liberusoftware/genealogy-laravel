<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Research\Actions\CreateResearchEntry as CreateResearchEntryAction;
use Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource;

final class CreateResearchEntry extends CreateRecord
{
    protected static string $resource = ResearchEntryResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateResearchEntryAction::class)->execute($data);
    }
}
