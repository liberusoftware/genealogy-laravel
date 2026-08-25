<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Research\Actions\CreateResearchProject as CreateResearchProjectAction;
use Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource;

final class CreateResearchProject extends CreateRecord
{
    protected static string $resource = ResearchProjectResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateResearchProjectAction::class)->execute($data);
    }
}
