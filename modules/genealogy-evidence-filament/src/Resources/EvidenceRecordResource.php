<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource\Pages\CreateEvidenceRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource\Pages\EditEvidenceRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource\Pages\ListEvidenceRecords;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;

final class EvidenceRecordResource extends Resource
{
    protected static ?string $model = EvidenceRecord::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('kind')->options(array_combine(EvidenceRecord::KINDS, EvidenceRecord::KINDS))->required(),
            TextInput::make('repository')->maxLength(255),
            Textarea::make('citation')->maxLength(10000),
            Textarea::make('extract')->maxLength(10000),
            Textarea::make('assertion')->maxLength(10000),
            Textarea::make('proof_conclusion')->maxLength(10000),
            TextInput::make('confidence')->numeric()->minValue(0)->maxValue(100)->default(0),
            TextInput::make('source_url')->url()->maxLength(2048),
            DatePicker::make('event_date'),
            TextInput::make('subject_person_id')->uuid(),
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
            TextColumn::make('kind')->badge()->sortable(),
            TextColumn::make('confidence')->numeric()->sortable(),
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
            'index' => ListEvidenceRecords::route('/'),
            'create' => CreateEvidenceRecord::route('/create'),
            'edit' => EditEvidenceRecord::route('/{record}/edit'),
        ];
    }
}
