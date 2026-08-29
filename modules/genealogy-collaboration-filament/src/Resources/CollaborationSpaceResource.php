<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Collaboration\Actions\DeleteCollaborationSpace;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource\Pages\CreateCollaborationSpace;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource\Pages\EditCollaborationSpace;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource\Pages\ListCollaborationSpaces;
use Liberu\Genealogy\Collaboration\Models\CollaborationSpace;

final class CollaborationSpaceResource extends Resource
{
    protected static ?string $model = CollaborationSpace::class;

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
            DeleteAction::make()->action(fn (CollaborationSpace $record): mixed => app(DeleteCollaborationSpace::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListCollaborationSpaces::route('/'),
            'create' => CreateCollaborationSpace::route('/create'),
            'edit' => EditCollaborationSpace::route('/{record}/edit'),
        ];
    }
}
