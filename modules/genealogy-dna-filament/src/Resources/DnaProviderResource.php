<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Dna\Actions\DeleteDnaProvider;
use Liberu\Genealogy\Dna\Filament\Resources\DnaProviderResource\Pages\CreateDnaProvider;
use Liberu\Genealogy\Dna\Filament\Resources\DnaProviderResource\Pages\EditDnaProvider;
use Liberu\Genealogy\Dna\Filament\Resources\DnaProviderResource\Pages\ListDnaProviders;
use Liberu\Genealogy\Dna\Models\DnaProvider;

final class DnaProviderResource extends Resource
{
    protected static ?string $model = DnaProvider::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'DNA & Matching';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('slug')->maxLength(255),
            Select::make('status')->options(array_combine(DnaProvider::STATUSES, DnaProvider::STATUSES))->required(),
            TextInput::make('website')->url()->maxLength(2048),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('slug')->searchable()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('kits_count')->counts('kits')->label('Kits')->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->action(fn (DnaProvider $record): mixed => app(DeleteDnaProvider::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListDnaProviders::route('/'),
            'create' => CreateDnaProvider::route('/create'),
            'edit' => EditDnaProvider::route('/{record}/edit'),
        ];
    }
}
