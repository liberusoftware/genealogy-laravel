<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Filament\Resources;

use Filament\Actions\Action;
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
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\TreeViewer\Actions\DeleteTreeView;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages\CreateTreeView;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages\EditTreeView;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages\ListTreeViews;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages\TreeGraph;
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
            Select::make('status')->options(array_combine(TreeView::STATUSES, TreeView::STATUSES))->required(),
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
            Action::make('graph')
                ->label('Open graph')
                ->icon('heroicon-o-share')
                ->url(fn (TreeView $record): string => self::getUrl('graph', ['record' => $record])),
            EditAction::make(),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteTreeView::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListTreeViews::route('/'),
            'create' => CreateTreeView::route('/create'),
            'edit' => EditTreeView::route('/{record}/edit'),
            'graph' => TreeGraph::route('/{record}/graph'),
        ];
    }
}
