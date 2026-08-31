<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Dna\Actions\DeleteDnaGroup;
use Liberu\Genealogy\Dna\Filament\Resources\DnaGroupResource\Pages\CreateDnaGroup;
use Liberu\Genealogy\Dna\Filament\Resources\DnaGroupResource\Pages\EditDnaGroup;
use Liberu\Genealogy\Dna\Filament\Resources\DnaGroupResource\Pages\ListDnaGroups;
use Liberu\Genealogy\Dna\Models\DnaGroup;

final class DnaGroupResource extends Resource
{
    protected static ?string $model = DnaGroup::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';

    protected static string|\UnitEnum|null $navigationGroup = 'DNA & Matching';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('status')->options([
                'draft' => 'Draft',
                'active' => 'Active',
                'archived' => 'Archived',
            ])->required(),
            Textarea::make('description')->columnSpanFull(),
            Textarea::make('metadata')->json()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('matches_count')->counts('matches')->label('Matches')->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->action(fn (DnaGroup $record): mixed => app(DeleteDnaGroup::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListDnaGroups::route('/'),
            'create' => CreateDnaGroup::route('/create'),
            'edit' => EditDnaGroup::route('/{record}/edit'),
        ];
    }
}
