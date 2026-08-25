<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Liberu\Genealogy\Evidence\Models\Citation;

final class CitationResource extends EvidenceEntityResource
{
    protected static ?string $model = Citation::class;

    protected static ?string $navigationLabel = 'Citations';
}
