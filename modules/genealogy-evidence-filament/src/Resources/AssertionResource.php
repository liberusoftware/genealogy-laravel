<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Liberu\Genealogy\Evidence\Models\Assertion;

final class AssertionResource extends EvidenceEntityResource
{
    protected static ?string $model = Assertion::class;

    protected static ?string $navigationLabel = 'Assertions';
}
