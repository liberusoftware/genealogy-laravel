<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Filament\Resources;

use Filament\Actions\Action;
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
use Liberu\Genealogy\ImportExport\Actions\DeleteDataTransfer;
use Liberu\Genealogy\ImportExport\Actions\UndoDataTransfer;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages\CreateDataTransfer;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages\EditDataTransfer;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages\ListDataTransfers;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;

final class DataTransferResource extends Resource
{
    protected static ?string $model = DataTransfer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Data & Media';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('format')->options([
                'gedcom' => 'GEDCOM 5.5.1',
                'gedcom-7' => 'GEDCOM 7.0',
                'gedcom-x' => 'GEDCOM X JSON',
                'gramps-xml' => 'GRAMPS XML',
            ])->required(),
            Select::make('direction')->options([
                'import' => 'Import',
                'export' => 'Export',
            ])->required(),
            Select::make('status')->options([
                'draft' => 'Draft',
                'active' => 'Active',
            'completed' => 'Completed',
            'rolled_back' => 'Rolled back',
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
            Action::make('undo')
                ->label('Undo import')
                ->requiresConfirmation()
                ->visible(fn (DataTransfer $record): bool => $record->status === 'completed' && $record->direction === 'import')
                ->action(fn (DataTransfer $record): DataTransfer => app(UndoDataTransfer::class)->execute($record)),
            EditAction::make(),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteDataTransfer::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListDataTransfers::route('/'),
            'create' => CreateDataTransfer::route('/create'),
            'edit' => EditDataTransfer::route('/{record}/edit'),
        ];
    }
}
