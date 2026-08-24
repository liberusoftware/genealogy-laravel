<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource;

final class EditGenealogyReport extends EditRecord
{
    protected static string $resource = GenealogyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
