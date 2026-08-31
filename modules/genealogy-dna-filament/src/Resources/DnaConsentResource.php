<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources;

use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Dna\Filament\Resources\DnaConsentResource\Pages\ListDnaConsents;
use Liberu\Genealogy\Dna\Models\DnaConsent;

final class DnaConsentResource extends Resource
{
    protected static ?string $model = DnaConsent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string|\UnitEnum|null $navigationGroup = 'DNA & Matching';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('kit_id')->label('Kit')->sortable(),
            TextColumn::make('scope')->sortable(),
            IconColumn::make('granted')->boolean()->sortable(),
            TextColumn::make('policy_version')->sortable(),
            TextColumn::make('granted_at')->dateTime()->sortable(),
            TextColumn::make('revoked_at')->dateTime()->sortable(),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListDnaConsents::route('/')];
    }
}
