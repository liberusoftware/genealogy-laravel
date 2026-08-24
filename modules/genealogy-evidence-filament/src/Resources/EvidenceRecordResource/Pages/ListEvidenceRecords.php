<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource;

final class ListEvidenceRecords extends ListRecords
{
    protected static string $resource = EvidenceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
