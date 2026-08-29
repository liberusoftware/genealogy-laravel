<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Filament\Resources\PersonResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\People\Actions\CreateMergeCandidate;
use Liberu\Genealogy\People\Actions\CreatePersonIdentity;
use Liberu\Genealogy\People\Actions\CreatePersonLifeEvent;
use Liberu\Genealogy\People\Actions\CreatePersonName;
use Liberu\Genealogy\People\Actions\DeletePersonSupportingRecord;
use Liberu\Genealogy\People\Actions\UpdatePersonSupportingRecord;

abstract class PersonSupportingRelationManager extends RelationManager
{
    protected static string $relationship;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('type')->required()->maxLength(100),
            TextInput::make('given_name')->visible(fn (): bool => static::$relationship === 'names')->maxLength(255),
            TextInput::make('family_name')->visible(fn (): bool => static::$relationship === 'names')->maxLength(255),
            TextInput::make('prefix')->visible(fn (): bool => static::$relationship === 'names')->maxLength(50),
            TextInput::make('suffix')->visible(fn (): bool => static::$relationship === 'names')->maxLength(50),
            TextInput::make('value')->visible(fn (): bool => static::$relationship === 'identities')->required()->maxLength(500),
            TextInput::make('label')->visible(fn (): bool => static::$relationship === 'identities')->maxLength(255),
            DatePicker::make('date')->visible(fn (): bool => static::$relationship === 'lifeEvents'),
            TextInput::make('place')->visible(fn (): bool => static::$relationship === 'lifeEvents')->maxLength(255),
            Textarea::make('description')->visible(fn (): bool => static::$relationship === 'lifeEvents'),
            TextInput::make('candidate_person_id')->visible(fn (): bool => static::$relationship === 'mergeCandidates')->uuid()->required(),
            TextInput::make('score')->visible(fn (): bool => static::$relationship === 'mergeCandidates')->numeric()->minValue(0)->maxValue(1),
            Textarea::make('reason')->visible(fn (): bool => static::$relationship === 'mergeCandidates'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->badge(),
                TextColumn::make('given_name')->toggleable(),
                TextColumn::make('family_name')->toggleable(),
                TextColumn::make('value')->toggleable(),
                TextColumn::make('date')->date()->toggleable(),
                TextColumn::make('status')->badge()->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->using(function (array $data): Model {
                    $data['person_id'] = $this->getOwnerRecord()->getKey();
                    $action = match (static::$relationship) {
                        'names' => CreatePersonName::class,
                        'identities' => CreatePersonIdentity::class,
                        'lifeEvents' => CreatePersonLifeEvent::class,
                        'mergeCandidates' => CreateMergeCandidate::class,
                        default => throw new \LogicException('Unsupported person supporting relation.'),
                    };

                    return app($action)->execute($data);
                }),
            ])
            ->recordActions([
                EditAction::make()->using(
                    fn (Model $record, array $data): Model => app(UpdatePersonSupportingRecord::class)->execute($record, $data),
                ),
                DeleteAction::make()->action(
                    fn (Model $record): mixed => app(DeletePersonSupportingRecord::class)->execute($record),
                ),
            ]);
    }
}
