<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Dna\Actions\DeleteDnaKit;
use Liberu\Genealogy\Dna\Actions\GrantDnaConsent;
use Liberu\Genealogy\Dna\Actions\RevokeDnaKit;
use Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource\Pages\CreateDnaKit;
use Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource\Pages\EditDnaKit;
use Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource\Pages\ListDnaKits;
use Liberu\Genealogy\Dna\Models\DnaKit;

final class DnaKitResource extends Resource
{
    protected static ?string $model = DnaKit::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'DNA & Matching';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('provider')->maxLength(100),
            Select::make('provider_id')->relationship('dnaProvider', 'name')->searchable()->preload()->nullable(),
            TextInput::make('external_id')->maxLength(255),
            TextInput::make('person_id')->uuid(),
            TextInput::make('test_type')->maxLength(100),
            Select::make('consent_status')->options(array_combine(DnaKit::CONSENT_STATUSES, DnaKit::CONSENT_STATUSES))->disabled(),
            Select::make('status')->options(array_combine(DnaKit::STATUSES, DnaKit::STATUSES))->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('provider')->sortable(),
            TextColumn::make('consent_status')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            EditAction::make(),
            Action::make('grant-consent')->form([
                TextInput::make('scope')->required()->maxLength(100),
                TextInput::make('policy_version')->maxLength(100),
            ])->visible(fn (DnaKit $record): bool => $record->consent_status !== 'revoked')->action(fn (DnaKit $record, array $data): mixed => app(GrantDnaConsent::class)->execute($record, $data['scope'], $data['policy_version'] ?? null)),
            Action::make('revoke-consent')->form([TextInput::make('reason')->required()->maxLength(1000)])->visible(fn (DnaKit $record): bool => $record->consent_status === 'granted')->requiresConfirmation()->action(fn (DnaKit $record, array $data): mixed => app(RevokeDnaKit::class)->execute($record, $data['reason'])),
            DeleteAction::make()->action(fn (DnaKit $record): mixed => app(DeleteDnaKit::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListDnaKits::route('/'),
            'create' => CreateDnaKit::route('/create'),
            'edit' => EditDnaKit::route('/{record}/edit'),
        ];
    }
}
