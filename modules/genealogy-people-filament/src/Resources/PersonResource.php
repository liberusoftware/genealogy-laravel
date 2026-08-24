<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\People\Filament\Resources\PersonResource\Pages\CreatePerson;
use Liberu\Genealogy\People\Filament\Resources\PersonResource\Pages\EditPerson;
use Liberu\Genealogy\People\Filament\Resources\PersonResource\Pages\ListPeople;
use Liberu\Genealogy\People\Models\Person;

final class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('given_name')->required()->maxLength(255),
            TextInput::make('family_name')->maxLength(255),
            TextInput::make('display_name')->maxLength(255),
            TextInput::make('sex')->maxLength(1),
            DatePicker::make('birth_date'),
            DatePicker::make('death_date')->afterOrEqual('birth_date'),
            TextInput::make('birth_place')->maxLength(255),
            TextInput::make('death_place')->maxLength(255),
            Toggle::make('is_public')->default(false),
            Textarea::make('metadata')->json()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('display_name')->label('Name')->getStateUsing(fn (Person $record): string => $record->display_name)->searchable(),
            TextColumn::make('birth_date')->date()->sortable(),
            TextColumn::make('death_date')->date()->sortable(),
            TextColumn::make('birth_place')->searchable(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListPeople::route('/'),
            'create' => CreatePerson::route('/create'),
            'edit' => EditPerson::route('/{record}/edit'),
        ];
    }
}
