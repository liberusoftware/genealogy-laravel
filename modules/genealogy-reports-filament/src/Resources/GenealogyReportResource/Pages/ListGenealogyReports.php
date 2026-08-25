<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource;

final class ListGenealogyReports extends ListRecords
{
    protected static string $resource = GenealogyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
