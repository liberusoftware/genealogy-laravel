<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Dna\Actions\DeleteDnaMatch;
use Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource\Pages\CreateDnaMatch;
use Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource\Pages\EditDnaMatch;
use Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource\Pages\ListDnaMatches;
use Liberu\Genealogy\Dna\Models\DnaMatch;

final class DnaMatchResource extends Resource
{
    protected static ?string $model = DnaMatch::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-share';

    protected static string|\UnitEnum|null $navigationGroup = 'DNA & Matching';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('kit_id')->required()->uuid(),
            TextInput::make('external_id')->required()->maxLength(255),
            TextInput::make('display_name')->maxLength(255),
            TextInput::make('predicted_relationship')->maxLength(100),
            TextInput::make('confidence')->numeric()->minValue(0)->maxValue(100),
            TextInput::make('total_cm')->numeric()->minValue(0),
            TextInput::make('shared_segments')->numeric()->minValue(0),
            Select::make('status')->options(array_combine(DnaMatch::STATUSES, DnaMatch::STATUSES))->required(),
            Toggle::make('is_private')->default(false),
            Textarea::make('notes')->columnSpanFull(),
            Textarea::make('metadata')->json()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('display_name')->label('Match')->searchable()->sortable(),
            TextColumn::make('predicted_relationship')->label('Relationship')->badge(),
            TextColumn::make('confidence')->suffix('%')->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            IconColumn::make('is_private')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->action(fn (DnaMatch $record): mixed => app(DeleteDnaMatch::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListDnaMatches::route('/'),
            'create' => CreateDnaMatch::route('/create'),
            'edit' => EditDnaMatch::route('/{record}/edit'),
        ];
    }
}
