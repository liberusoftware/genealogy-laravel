<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages\CreateTreeView;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages\EditTreeView;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages\ListTreeViews;
use Liberu\Genealogy\TreeViewer\Models\TreeView;

final class TreeViewResource extends Resource
{
    protected static ?string $model = TreeView::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('root_person_id')->label('Root person ID')->uuid()->nullable(),
            Select::make('status')->options([
                'draft' => 'Draft',
                'active' => 'Active',
                'completed' => 'Completed',
            ])->required(),
            Toggle::make('is_public')->default(false),
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
            'index' => ListTreeViews::route('/'),
            'create' => CreateTreeView::route('/create'),
            'edit' => EditTreeView::route('/{record}/edit'),
        ];
    }
}
