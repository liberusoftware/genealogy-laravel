<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\AssertionResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\AssertionResource;
use Liberu\Genealogy\Evidence\Filament\Resources\Pages\CreateEvidenceEntityRecord;

final class CreateAssertion extends CreateEvidenceEntityRecord
{
    protected static string $resource = AssertionResource::class;
}
