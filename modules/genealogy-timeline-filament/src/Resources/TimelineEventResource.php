<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource\Pages\CreateTimelineEvent;
use Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource\Pages\EditTimelineEvent;
use Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource\Pages\ListTimelineEvents;
use Liberu\Genealogy\Timeline\Models\TimelineEvent;

final class TimelineEventResource extends Resource
{
    protected static ?string $model = TimelineEvent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('kind')->options(array_combine(TimelineEvent::KINDS, TimelineEvent::KINDS))->required(),
            Select::make('status')->options([
                'draft' => 'Draft',
                'active' => 'Active',
                'completed' => 'Completed',
            ])->required(),
            TextInput::make('subject_person_id')->uuid()->nullable(),
            TextInput::make('family_key')->maxLength(255)->nullable(),
            Filament\Forms\Components\DatePicker::make('event_date')->nullable(),
            Filament\Forms\Components\DatePicker::make('date_start')->nullable(),
            Filament\Forms\Components\DatePicker::make('date_end')->nullable(),
            Select::make('date_precision')->options(array_combine(TimelineEvent::DATE_PRECISIONS, TimelineEvent::DATE_PRECISIONS))->required(),
            TextInput::make('place_id')->uuid()->nullable(),
            Filament\Forms\Components\Textarea::make('description')->nullable(),
            Filament\Forms\Components\Textarea::make('historical_context')->nullable(),
            TextInput::make('conflict_group')->maxLength(255)->nullable(),
            TextInput::make('confidence')->numeric()->minValue(0)->maxValue(100)->nullable(),
            TextInput::make('source_reference')->maxLength(255)->nullable(),
            Filament\Forms\Components\Toggle::make('is_private'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('kind')->badge()->sortable(),
            TextColumn::make('event_date')->date()->sortable(),
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
            'index' => ListTimelineEvents::route('/'),
            'create' => CreateTimelineEvent::route('/create'),
            'edit' => EditTimelineEvent::route('/{record}/edit'),
        ];
    }
}
