<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources;

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
use Liberu\Genealogy\Research\Actions\DeleteResearchProject;
use Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource\Pages\CreateResearchProject;
use Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource\Pages\EditResearchProject;
use Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource\Pages\ListResearchProjects;
use Liberu\Genealogy\Research\Models\ResearchProject;

final class ResearchProjectResource extends Resource
{
    protected static ?string $model = ResearchProject::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('status')->options(array_combine(
                ResearchProject::STATUSES,
                array_map(fn (string $status): string => str_replace('_', ' ', ucfirst($status)), ResearchProject::STATUSES),
            ))->required(),
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
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteResearchProject::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListResearchProjects::route('/'),
            'create' => CreateResearchProject::route('/create'),
            'edit' => EditResearchProject::route('/{record}/edit'),
        ];
    }
}
