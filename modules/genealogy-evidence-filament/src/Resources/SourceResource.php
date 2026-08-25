<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Liberu\Genealogy\Evidence\Models\Source;

final class SourceResource extends EvidenceEntityResource
{
    protected static ?string $model = Source::class;

    protected static ?string $navigationLabel = 'Sources';
}
