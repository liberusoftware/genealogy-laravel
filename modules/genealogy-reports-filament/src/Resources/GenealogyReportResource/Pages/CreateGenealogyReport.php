<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource;

final class CreateGenealogyReport extends CreateRecord
{
    protected static string $resource = GenealogyReportResource::class;
}
