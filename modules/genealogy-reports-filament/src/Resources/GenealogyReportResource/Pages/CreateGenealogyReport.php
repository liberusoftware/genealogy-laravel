<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Reports\Actions\CreateGenealogyReport as CreateGenealogyReportAction;
use Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource;

final class CreateGenealogyReport extends CreateRecord
{
    protected static string $resource = GenealogyReportResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateGenealogyReportAction::class)->execute($data);
    }
}
