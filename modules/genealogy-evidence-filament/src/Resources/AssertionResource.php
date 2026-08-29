<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Filament\Resources\Pages\PageRegistration;
use Liberu\Genealogy\Evidence\Filament\Resources\AssertionResource\Pages\CreateAssertion;
use Liberu\Genealogy\Evidence\Filament\Resources\AssertionResource\Pages\EditAssertion;
use Liberu\Genealogy\Evidence\Filament\Resources\AssertionResource\Pages\ListAssertions;
use Liberu\Genealogy\Evidence\Models\Assertion;

final class AssertionResource extends EvidenceEntityResource
{
    protected static ?string $model = Assertion::class;

    protected static ?string $navigationLabel = 'Assertions';

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListAssertions::route('/'), 'create' => CreateAssertion::route('/create'), 'edit' => EditAssertion::route('/{record}/edit')];
    }
}
