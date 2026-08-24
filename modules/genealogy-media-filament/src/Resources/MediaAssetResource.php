<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource\Pages\CreateMediaAsset;
use Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource\Pages\EditMediaAsset;
use Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource\Pages\ListMediaAssets;
use Liberu\Genealogy\Media\Models\MediaAsset;

final class MediaAssetResource extends Resource
{
    protected static ?string $model = MediaAsset::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('kind')->options(array_combine(MediaAsset::KINDS, MediaAsset::KINDS))->required(),
            Select::make('status')->options([
                'draft' => 'Draft',
                'active' => 'Active',
                'completed' => 'Completed',
            ])->required(),
            TextInput::make('storage_disk')->maxLength(100)->nullable(),
            TextInput::make('storage_path')->maxLength(2000)->nullable(),
            TextInput::make('mime_type')->maxLength(255)->nullable(),
            TextInput::make('byte_size')->numeric()->minValue(0)->nullable(),
            TextInput::make('checksum')->maxLength(128)->nullable(),
            Filament\Forms\Components\DateTimePicker::make('captured_at')->nullable(),
            TextInput::make('captured_place_id')->uuid()->nullable(),
            Filament\Forms\Components\Textarea::make('transcription')->nullable(),
            Select::make('transcription_status')->options(array_combine(MediaAsset::TRANSCRIPTION_STATUSES, MediaAsset::TRANSCRIPTION_STATUSES))->required(),
            TextInput::make('transcription_language')->maxLength(16)->nullable(),
            TextInput::make('rights_holder')->maxLength(255)->nullable(),
            Select::make('rights_status')->options(array_combine(MediaAsset::RIGHTS_STATUSES, MediaAsset::RIGHTS_STATUSES))->nullable(),
            TextInput::make('license_url')->url()->maxLength(2000)->nullable(),
            Filament\Forms\Components\DatePicker::make('rights_expires_at')->nullable(),
            Filament\Forms\Components\Toggle::make('is_public'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('kind')->badge()->sortable(),
            TextColumn::make('mime_type')->toggleable(),
            TextColumn::make('rights_status')->badge()->toggleable(),
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
            'index' => ListMediaAssets::route('/'),
            'create' => CreateMediaAsset::route('/create'),
            'edit' => EditMediaAsset::route('/{record}/edit'),
        ];
    }
}
