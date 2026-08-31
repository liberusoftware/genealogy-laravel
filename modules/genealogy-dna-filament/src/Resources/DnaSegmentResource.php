<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Dna\Actions\DeleteDnaSegment;
use Liberu\Genealogy\Dna\Filament\Resources\DnaSegmentResource\Pages\CreateDnaSegment;
use Liberu\Genealogy\Dna\Filament\Resources\DnaSegmentResource\Pages\EditDnaSegment;
use Liberu\Genealogy\Dna\Filament\Resources\DnaSegmentResource\Pages\ListDnaSegments;
use Liberu\Genealogy\Dna\Models\DnaSegment;

final class DnaSegmentResource extends Resource
{
    protected static ?string $model = DnaSegment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'DNA & Matching';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('match_id')->uuid()->required(),
            TextInput::make('chromosome')->integer()->minValue(1)->maxValue(99)->required(),
            TextInput::make('start_position')->integer()->minValue(0)->required(),
            TextInput::make('end_position')->integer()->minValue(1)->required(),
            TextInput::make('centimorgans')->numeric()->minValue(0),
            TextInput::make('snps')->integer()->minValue(0),
            TextInput::make('side')->maxLength(50),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('match_id')->label('Match')->sortable(),
            TextColumn::make('chromosome')->sortable(),
            TextColumn::make('start_position')->sortable(),
            TextColumn::make('end_position')->sortable(),
            TextColumn::make('centimorgans')->sortable(),
            TextColumn::make('side')->badge(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->action(fn (DnaSegment $record): mixed => app(DeleteDnaSegment::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListDnaSegments::route('/'),
            'create' => CreateDnaSegment::route('/create'),
            'edit' => EditDnaSegment::route('/{record}/edit'),
        ];
    }
}
