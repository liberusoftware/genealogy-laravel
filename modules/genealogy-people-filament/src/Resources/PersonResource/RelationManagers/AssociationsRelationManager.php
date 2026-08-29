<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Filament\Resources\PersonResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\People\Actions\CreatePersonAssociation;
use Liberu\Genealogy\People\Actions\DeletePersonAssociation;
use Liberu\Genealogy\People\Actions\UpdatePersonAssociation;

final class AssociationsRelationManager extends RelationManager
{
    protected static string $relationship = 'associations';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('associated_person_id')->label('Person ID')->uuid(),
            TextInput::make('associated_external_id')->label('External reference')->maxLength(255),
            TextInput::make('relationship')->required()->maxLength(255),
            Textarea::make('description'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('relationship')->badge(),
            TextColumn::make('associated_person.display_name')->label('Associated person')->placeholder('Unresolved'),
            TextColumn::make('associated_external_id')->label('External reference'),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->headerActions([
            CreateAction::make()->using(function (array $data): Model {
                $data['person_id'] = $this->getOwnerRecord()->getKey();

                return app(CreatePersonAssociation::class)->execute($data);
            }),
        ])->recordActions([
            EditAction::make()->using(fn (Model $record, array $data): Model => app(UpdatePersonAssociation::class)->execute($record, $data)),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeletePersonAssociation::class)->execute($record)),
        ]);
    }
}
