<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Filament\Resources;

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
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Places\Actions\DeletePlace;
use Liberu\Genealogy\Places\Filament\Resources\PlaceResource\Pages\CreatePlace;
use Liberu\Genealogy\Places\Filament\Resources\PlaceResource\Pages\EditPlace;
use Liberu\Genealogy\Places\Filament\Resources\PlaceResource\Pages\ListPlaces;
use Liberu\Genealogy\Places\Models\Place;

final class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('parent_id')->uuid(),
            Textarea::make('historical_names')->json(),
            TextInput::make('latitude')->numeric()->minValue(-90)->maxValue(90),
            TextInput::make('longitude')->numeric()->minValue(-180)->maxValue(180),
            TextInput::make('jurisdiction')->maxLength(255),
            Select::make('status')->options(array_combine(Place::STATUSES, array_map(
                static fn (string $status): string => ucfirst($status),
                Place::STATUSES,
            )))->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeletePlace::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListPlaces::route('/'),
            'create' => CreatePlace::route('/create'),
            'edit' => EditPlace::route('/{record}/edit'),
        ];
    }
}
