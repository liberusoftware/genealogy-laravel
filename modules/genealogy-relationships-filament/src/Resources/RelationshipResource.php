<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Relationships\Actions\DeleteRelationship;
use Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource\Pages\CreateRelationship;
use Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource\Pages\EditRelationship;
use Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource\Pages\ListRelationships;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class RelationshipResource extends Resource
{
    protected static ?string $model = Relationship::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('person_id')->required()->uuid(),
            TextInput::make('related_person_id')->required()->uuid(),
            Select::make('type')->options(array_combine(Relationship::TYPES, Relationship::TYPES))->required(),
            TextInput::make('confidence')->numeric()->minValue(0)->maxValue(100)->default(100),
            TextInput::make('metadata')->json(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('type')->badge()->sortable(),
            TextColumn::make('person_id')->label('Person'),
            TextColumn::make('related_person_id')->label('Related person'),
            TextColumn::make('confidence')->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteRelationship::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListRelationships::route('/'),
            'create' => CreateRelationship::route('/create'),
            'edit' => EditRelationship::route('/{record}/edit'),
        ];
    }
}
