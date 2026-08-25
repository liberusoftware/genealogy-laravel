<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Liberu\Genealogy\Evidence\Models\Extract;

final class ExtractResource extends EvidenceEntityResource
{
    protected static ?string $model = Extract::class;

    protected static ?string $navigationLabel = 'Extracts';
}
