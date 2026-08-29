<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\ExtractResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\ExtractResource;
use Liberu\Genealogy\Evidence\Filament\Resources\Pages\CreateEvidenceEntityRecord;

final class CreateExtract extends CreateEvidenceEntityRecord
{
    protected static string $resource = ExtractResource::class;
}
