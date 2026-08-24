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
            Select::make('status')->options([
                'draft' => 'Draft',
                'active' => 'Active',
                'completed' => 'Completed',
            ])->required(),
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
            DeleteAction::make(),
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
