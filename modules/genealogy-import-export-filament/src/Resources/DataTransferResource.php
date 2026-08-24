<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages\CreateDataTransfer;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages\EditDataTransfer;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages\ListDataTransfers;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;

final class DataTransferResource extends Resource
{
    protected static ?string $model = DataTransfer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('format')->options([
                'gedcom' => 'GEDCOM 5.5.1',
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
            'index' => ListDataTransfers::route('/'),
            'create' => CreateDataTransfer::route('/create'),
            'edit' => EditDataTransfer::route('/{record}/edit'),
        ];
    }
}
