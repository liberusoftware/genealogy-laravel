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
use Liberu\Genealogy\Dna\Actions\DeleteDnaRelationship;
use Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource\Pages\CreateDnaRelationship;
use Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource\Pages\EditDnaRelationship;
use Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource\Pages\ListDnaRelationships;
use Liberu\Genealogy\Dna\Models\DnaRelationship;

final class DnaRelationshipResource extends Resource
{
    protected static ?string $model = DnaRelationship::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static string|\UnitEnum|null $navigationGroup = 'DNA & Matching';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('match_id')->uuid()->required(),
            TextInput::make('person_id')->uuid()->required(),
            TextInput::make('relationship_type')->required()->maxLength(100),
            TextInput::make('confidence')->numeric()->minValue(0)->maxValue(100),
            Select::make('status')->options(array_combine(DnaRelationship::STATUSES, DnaRelationship::STATUSES))->required(),
            Textarea::make('rationale')->columnSpanFull(),
            Textarea::make('metadata')->json()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('match_id')->label('Match')->sortable(),
            TextColumn::make('person_id')->label('Person')->sortable(),
            TextColumn::make('relationship_type')->badge(),
            TextColumn::make('confidence')->suffix('%')->sortable(),
            TextColumn::make('status')->badge()->sortable(),
        ])->recordActions([EditAction::make(), DeleteAction::make()->action(fn (DnaRelationship $record): mixed => app(DeleteDnaRelationship::class)->execute($record))]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListDnaRelationships::route('/'), 'create' => CreateDnaRelationship::route('/create'), 'edit' => EditDnaRelationship::route('/{record}/edit')];
    }
}
