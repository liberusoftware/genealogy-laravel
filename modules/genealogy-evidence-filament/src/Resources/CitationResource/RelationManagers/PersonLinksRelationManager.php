<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\CitationResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Evidence\Actions\CreateCitationLink;
use Liberu\Genealogy\Evidence\Actions\DeleteCitationLink;
use Liberu\Genealogy\Evidence\Actions\UpdateCitationLink;
use Liberu\Genealogy\Evidence\Models\CitationLink;

final class PersonLinksRelationManager extends RelationManager
{
    protected static string $relationship = 'personLinks';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('subject_person_id')->label('Person ID')->uuid()->required(),
            Select::make('group')->options(array_combine(CitationLink::GROUPS, CitationLink::GROUPS))->default('indi')->required(),
            TextInput::make('page')->maxLength(255),
            TextInput::make('quality')->maxLength(255),
            Textarea::make('text'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('subject.display_name')->label('Person'),
            TextColumn::make('group')->badge(),
            TextColumn::make('page'),
            TextColumn::make('quality_label')->label('Quality'),
        ])->headerActions([
            CreateAction::make()->using(function (array $data): Model {
                $data['citation_id'] = $this->getOwnerRecord()->getKey();

                return app(CreateCitationLink::class)->execute($data);
            }),
        ])->recordActions([
            EditAction::make()->using(fn (Model $record, array $data): Model => app(UpdateCitationLink::class)->execute($record, $data)),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteCitationLink::class)->execute($record)),
        ]);
    }
}
