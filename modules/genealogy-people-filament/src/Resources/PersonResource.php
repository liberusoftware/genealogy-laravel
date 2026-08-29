<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\People\Actions\DeletePerson;
use Liberu\Genealogy\People\Actions\SetPersonLifeStatus;
use Liberu\Genealogy\People\Filament\Resources\PersonResource\Pages\CreatePerson;
use Liberu\Genealogy\People\Filament\Resources\PersonResource\Pages\EditPerson;
use Liberu\Genealogy\People\Filament\Resources\PersonResource\Pages\ListPeople;
use Liberu\Genealogy\People\Filament\Resources\PersonResource\RelationManagers\AssociationsRelationManager;
use Liberu\Genealogy\People\Filament\Resources\PersonResource\RelationManagers\IdentitiesRelationManager;
use Liberu\Genealogy\People\Filament\Resources\PersonResource\RelationManagers\LifeEventsRelationManager;
use Liberu\Genealogy\People\Filament\Resources\PersonResource\RelationManagers\MergeCandidatesRelationManager;
use Liberu\Genealogy\People\Filament\Resources\PersonResource\RelationManagers\NamesRelationManager;
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
            Select::make('sex')->options([
                Person::GENDER_MALE => 'Male',
                Person::GENDER_FEMALE => 'Female',
                Person::GENDER_UNKNOWN => 'Unknown',
                Person::GENDER_OTHER => 'Other',
            ]),
            DatePicker::make('birth_date'),
            DatePicker::make('death_date')->afterOrEqual('birth_date'),
            TextInput::make('birth_place')->maxLength(255),
            TextInput::make('death_place')->maxLength(255),
            Toggle::make('is_public')->default(false),
            Textarea::make('attributes')->json()->columnSpanFull(),
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
            Action::make('setLifeStatus')
                ->label('Set life status')
                ->schema([
                    Select::make('status')->options(['living' => 'Living', 'deceased' => 'Deceased'])->required(),
                    DatePicker::make('death_date'),
                ])
                ->action(fn (Person $record, array $data): Person => app(SetPersonLifeStatus::class)->execute($record, $data['status'], $data['death_date'] ?? null)),
            EditAction::make(),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeletePerson::class)->execute($record)),
        ])->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make()->action(fn ($records): mixed => $records->each(
                    fn (Model $record): mixed => app(DeletePerson::class)->execute($record),
                )),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [NamesRelationManager::class, IdentitiesRelationManager::class, LifeEventsRelationManager::class, AssociationsRelationManager::class, MergeCandidatesRelationManager::class];
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
