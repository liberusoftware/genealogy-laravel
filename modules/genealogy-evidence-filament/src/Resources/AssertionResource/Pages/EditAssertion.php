<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\AssertionResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\AssertionResource;
use Liberu\Genealogy\Evidence\Filament\Resources\Pages\EditEvidenceEntityRecord;

final class EditAssertion extends EditEvidenceEntityRecord
{
    protected static string $resource = AssertionResource::class;
}
