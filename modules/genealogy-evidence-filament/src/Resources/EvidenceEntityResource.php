<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Evidence\Actions\DeleteEvidenceEntity;

class EvidenceEntityResource extends Resource
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Research & Evidence';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->visible(fn (): bool => in_array('name', (new static::$model())->getFillable(), true))->maxLength(255),
            TextInput::make('title')->visible(fn (): bool => in_array('title', (new static::$model())->getFillable(), true))->maxLength(255),
            Textarea::make('description')->visible(fn (): bool => in_array('description', (new static::$model())->getFillable(), true)),
            Textarea::make('content')->visible(fn (): bool => in_array('content', (new static::$model())->getFillable(), true)),
            Textarea::make('statement')->visible(fn (): bool => in_array('statement', (new static::$model())->getFillable(), true)),
            Textarea::make('conclusion')->visible(fn (): bool => in_array('conclusion', (new static::$model())->getFillable(), true)),
            TextInput::make('confidence')->numeric()->minValue(0)->maxValue(100)->visible(fn (): bool => in_array('confidence', (new static::$model())->getFillable(), true)),
            DatePicker::make('event_date')->visible(fn (): bool => in_array('event_date', (new static::$model())->getFillable(), true)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('id')->copyable()->searchable(),
            TextColumn::make('name')->searchable()->toggleable(),
            TextColumn::make('title')->searchable()->toggleable(),
            TextColumn::make('confidence')->numeric()->sortable()->toggleable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteEvidenceEntity::class)->execute($record)),
        ]);
    }
}
