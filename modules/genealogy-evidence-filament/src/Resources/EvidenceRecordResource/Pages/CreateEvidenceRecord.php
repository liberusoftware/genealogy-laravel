<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource;

final class CreateEvidenceRecord extends CreateRecord
{
    protected static string $resource = EvidenceRecordResource::class;
}
