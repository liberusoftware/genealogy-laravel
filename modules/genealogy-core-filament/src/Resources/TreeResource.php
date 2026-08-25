<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Actions\DeleteTree;
use Liberu\Genealogy\GenealogyCore\Filament\Resources\TreeResource\Pages\CreateTree;
use Liberu\Genealogy\GenealogyCore\Filament\Resources\TreeResource\Pages\EditTree;
use Liberu\Genealogy\GenealogyCore\Filament\Resources\TreeResource\Pages\ListTrees;
use Liberu\Genealogy\GenealogyCore\Models\Tree;

final class TreeResource extends Resource
{
    protected static ?string $model = Tree::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-share';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('status')->required()->in(['draft', 'active', 'archived']),
            Textarea::make('description')->columnSpanFull(),
            TextInput::make('root_person_id')->uuid(),
            Toggle::make('is_public')->default(false),
            Textarea::make('metadata')->json()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('status')->badge(),
            IconColumn::make('is_public')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteTree::class)->execute($record)),
        ])->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make()->action(fn ($records): mixed => $records->each(
                    fn (Model $record): mixed => app(DeleteTree::class)->execute($record),
                )),
            ]),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListTrees::route('/'),
            'create' => CreateTree::route('/create'),
            'edit' => EditTree::route('/{record}/edit'),
        ];
    }
}
