<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\AssertionResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\AssertionResource;
use Liberu\Genealogy\Evidence\Filament\Resources\Pages\ListEvidenceEntities;

final class ListAssertions extends ListEvidenceEntities
{
    protected static string $resource = AssertionResource::class;
}
