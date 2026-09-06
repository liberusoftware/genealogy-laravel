<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Filament\Actions\Action;
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
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Evidence\Actions\ArchiveEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\DeleteEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\ReviewEvidenceRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource\Pages\CreateEvidenceRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource\Pages\EditEvidenceRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource\Pages\ListEvidenceRecords;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;

final class EvidenceRecordResource extends Resource
{
    protected static ?string $model = EvidenceRecord::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Research & Evidence';

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
            Select::make('status')->options(array_combine(EvidenceRecord::STATUSES, array_map(
                static fn (string $status): string => ucfirst($status),
                EvidenceRecord::STATUSES,
            )))->required(),
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
            Action::make('review')
                ->label('Review')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->visible(fn (EvidenceRecord $record): bool => ! in_array($record->status, ['completed', 'archived'], true))
                ->action(fn (EvidenceRecord $record): EvidenceRecord => app(ReviewEvidenceRecord::class)->execute($record)),
            Action::make('archive')
                ->label('Archive')
                ->icon('heroicon-o-archive-box')
                ->requiresConfirmation()
                ->visible(fn (EvidenceRecord $record): bool => $record->status !== 'archived')
                ->action(fn (EvidenceRecord $record): EvidenceRecord => app(ArchiveEvidenceRecord::class)->execute($record)),
            EditAction::make(),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteEvidenceRecord::class)->execute($record)),
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
